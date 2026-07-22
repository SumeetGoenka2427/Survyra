# Phase 4 — Response Engine

## Context

Phases 1–3 built everything an admin needs to configure a survey, but nothing a real customer can see yet — `/s/{slug}` doesn't exist, the 15 question types Phase 2 built have never been rendered outside the admin builder, and the `LogicEngine` Phase 3 wrote has never been called from a live page. Phase 4 is Module 7 in `task.md` (Survey Responses) plus the parts of §14 ("Survey Response Engine") and §15 ("Response Storage") that make a published survey link actually collectible. It's also the reason Phase 3's version-lock (master gap #4) was deferred here: the `responses` table this phase creates is exactly what that lock needs to check against.

This is the highest-traffic, lowest-context surface in the whole product — most visits arrive from an SMS, WhatsApp message, QR scan, or email link on a phone, mid-errand, with zero patience for a slow page. Every decision below is weighed against that.

---

## 1. Scope (from `task.md` §7, §14, §15)

- A public survey page at `/s/{slug}` — no login, no guard, works for anyone with the link.
- **Every question answered saves immediately via AJAX** — never waits for a final Submit (§14's core requirement: no data loss, resumable, abandonment-trackable).
- Conditional logic (Phase 3's `LogicEngine`) actually decides what the next question is.
- On the last question, **Submit** finalizes the response, computes its score/sentiment against the primary question, and shows the matching Thank You screen (Phase 3's rules, finally rendered).
- Unified storage: `responses` + `response_answers` — never a table per survey, per §15's explicit instruction.

---

## 2. Database

- `responses` — uuid, client_id, survey_id, respondent_identifier (nullable), status (started/in_progress/completed/abandoned), device, browser, ip, source (direct/qr/sms/whatsapp/email), started_at, completed_at, score, sentiment.
- `response_answers` — response_id, question_id (FK `survey_questions`), answer (json — a string, number, or array depending on the question type), score (nullable, from `QuestionTypeContract::score()`), timestamps (upserted per question).

**Deliberately deferred, not forgotten**: master schema's `responses.contact_id` and `responses.campaign_id` reference tables (`contacts`, `campaigns`) that don't exist until Phase 5. Adding nullable FK columns now with nothing to point at is worse than adding them properly when Phase 5 lands — that phase's migration adds both columns with real constraints. Same reasoning for `location` (IP geolocation): resolving IP → city/country means an external API call, and calling out to a third party during page load directly contradicts this phase's "must load instantly" mandate. Nothing here loses the raw IP (it's still stored), just the enrichment - defer to Phase 6 (Analytics) if a real reporting need shows up.

---

## 3. Architecture decisions

- **Server-rendered fragments, not a JS framework.** The first question is rendered on the initial page load (fast paint, no JS round-trip to see anything). Each subsequent "answer" AJAX call returns a server-rendered HTML fragment for the next question, swapped into the DOM by a small vanilla JS file (`public/assets/js/survey.js`) — no client-side templating engine, matching the stack's existing "Blade + AJAX" decision from Phase 1.
- **Resume via a long-lived cookie, not accounts.** A cookie (`survyra_response_{slug}`) stores the response UUID. Returning to the link resumes wherever they left off; a cleared cookie just starts a fresh response. No magic links, no login — matches "no data loss, resume later" from §14 without inventing an identity system for anonymous respondents. (SMS/WhatsApp campaign-linked resume, tied to a known contact, is a Phase 5 concern once `contacts` exists.)
- **Forward-only navigation — no "back" button.** Simplifies validation and logic re-evaluation enormously (no need to un-wind a previously-triggered branch) and matches how most short feedback surveys already behave. If a respondent needs to correct an early answer, they restart — acceptable for an MVP anonymous-feedback tool.
- **Question visibility is computed on demand, not stored.** Whenever the engine needs to know "what's the next question," it re-evaluates every one of the survey's logic rules against the answers saved so far. No separate "visible/hidden" state column, no staleness risk — the DB already has everything needed (survey's rules + this response's answers), so the answer is always freshly derived. A survey has at most a handful of rules; this is cheap.
- **Default visibility resolves the `show` vs `hide` ambiguity from the blueprint's own examples.** §11 gives two examples that model the same "only ask unhappy customers for details" idea two different ways — one hides a follow-up when happy, one shows it only when unhappy. Resolved as: **a question that is the target of at least one `show` rule defaults to hidden** (only appears when that rule's conditions are met); **every other question defaults to visible**, and a `hide` rule targeting it conditionally removes it. Deterministic, and both blueprint examples map onto it correctly.
- **Per-answer validation reuses Phase 2's `QuestionTypeContract::validationRules()`**, called for the first time since it was written — an out-of-range NPS score or malformed email gets rejected with a 422 before it's ever saved, same as any other Laravel form.
- **Scoring and sentiment are computed once, at Submit, not live.** Each answer's own `score` is stored immediately (via `QuestionTypeContract::score()`) as it's saved, but the response's overall `score`/`sentiment` and the matching Thank You rule are only resolved when the respondent hits Submit, by reading the primary-score-question's stored answer and matching it against the survey's 3 `survey_thankyou_rules` bucket ranges. If the primary question was never answered (e.g. hidden by logic, or the survey has none set), the response falls back to the **neutral** rule — never silently defaults to positive, and never guesses at a review prompt it can't justify.
- **No UA-parsing library.** Device/browser capture is a lightweight inline regex against the User-Agent string (mobile/tablet/desktop, common browser names) — enough for the dashboard stat Phase 1 already scaffolded, without adding a dependency for something this coarse-grained.
- **Version-lock, finally implementable.** `SurveyService::hasResponses(Survey): bool` gates every structural edit (add/remove/reorder a question, change its type) in the Phase 3 builder — 403 once a survey has ≥1 response. Building a "publish a new version" duplication workflow is **not** in this phase; the safety-critical half (stop silently corrupting live data) ships now, the convenience half (an easy re-branch workflow) waits until a real client actually needs to edit a live survey.
- **Abandoned-response detection is a scheduled command, not a live check.** `php artisan responses:mark-abandoned`, scheduled hourly, flips any `started`/`in_progress` response untouched for 24h to `abandoned` — directly what §14 asks for ("track abandoned surveys") without adding queue/job infrastructure beyond what's already configured.
- **Rate limiting on every public endpoint** (`throttle:60,1` or similar) — the first genuinely public, unauthenticated write surface in the app, and §20 explicitly calls for it.

---

## 4. Implementation breakdown

**Models**: `Response` (belongsTo `Client`, `Survey`; hasMany `ResponseAnswer`), `ResponseAnswer` (belongsTo `Response`, `SurveyQuestion`).

**Services**:
- `ResponseService` — `startOrResume(Survey, ?string $cookieUuid, Request)` (creates or fetches the response, captures device/browser/ip/source), `saveAnswer(Response, SurveyQuestion, mixed $rawAnswer)` (validates via the question's `QuestionTypeContract`, upserts the `response_answers` row, stores its score), `nextQuestion(Response): ?SurveyQuestion` (the visibility-resolution logic described above), `submit(Response)` (finalizes status/completed_at, computes score/sentiment, resolves the matching `SurveyThankyouRule`).
- `SurveyVisibilityResolver` (or a method on `ResponseService` if it turns out small enough not to warrant its own class) — given a survey's logic rules and a response's answers-so-far, returns the set of hidden question IDs, using `LogicEngine::evaluate()` per rule.
- `UserAgentParser` — tiny inline helper, not a package, for device/browser capture.

**Controllers** (`App\Http\Controllers\Public\SurveyResponseController` — deliberately outside `Admin`/`Portal`, since this has neither guard): `show(string $slug)`, `answer(Request, string $slug)`, `submit(Request, string $slug)`.

**Routes** (new `routes/survey.php`, required from `web.php`, no auth middleware, rate-limited):
```
GET  /s/{slug}          survey.show
POST /s/{slug}/answer   survey.answer
POST /s/{slug}/submit   survey.submit
```

**Views**:
- `resources/views/survey/show.blade.php` — the public shell: theme-styled (colors/font/button style/progress bar/border radius/custom CSS pulled straight from the survey's assigned `SurveyTheme`), renders the current question partial, includes `survey.js`.
- `resources/views/survey-questions/{key}.blade.php` — the 15 partials `QuestionTypeContract::renderComponent()` has pointed at since Phase 2, built for real now (nps, csat, ces, radio, checkbox, dropdown, textbox, textarea, number, email, phone, date, rating-stars, emoji-rating, yes-no).
- `resources/views/survey/thankyou.blade.php` — renders the matched sentiment's message + conditional buttons (Google Review/Facebook/Instagram/website/coupon for positive; complaint form/support number/WhatsApp/manager contact for negative) exactly per the `SurveyThankyouRule` row.
- `resources/views/survey/unavailable.blade.php` — a graceful message for draft/archived/unknown slugs, instead of a raw 404.
- `public/assets/js/survey.js` — vanilla JS: posts each answer, swaps in the returned question/thank-you HTML, updates the progress indicator.

**Admin-side change**: `SurveyQuestionController` (Phase 3) gains the version-lock guard — every mutating action (`store`/`update`/`destroy`/`moveUp`/`moveDown`) checks `SurveyService::hasResponses($survey)` and aborts 403 if true, with a banner on the builder's Questions tab explaining why once a survey has real responses.

**Console command**: `app/Console/Commands/MarkAbandonedResponses.php`, registered on the schedule (hourly).

---

## 5. Tests (Pest)

- A fresh visit to a published survey's `/s/{slug}` creates a `started` response and renders the first visible question.
- Answering a question saves it, computes its score if scorable, and returns the correct next question — including a case where a `show` rule reveals a normally-hidden question and a case where a `hide` rule removes one.
- Submitting an invalid answer (NPS out of 0–10, malformed email) is rejected with a 422 and nothing is saved.
- Submitting the last question finalizes the response (`status`, `completed_at`) and returns the Thank You screen matching the primary question's score bucket — one test per sentiment (positive/neutral/negative), plus the "primary question never answered → falls back to neutral" case.
- The negative Thank You screen never renders a Google Review button, no matter what — reconfirms Phase 3's server-side lock actually holds at render time, not just at rule-save time.
- Returning with the same resume cookie continues the same response instead of starting a new one; a missing/invalid cookie starts fresh.
- A survey with zero responses can still have its questions edited; a survey with ≥1 response returns 403 on every structural edit route.
- `responses:mark-abandoned` flips old `started`/`in_progress` responses to `abandoned` and leaves recent ones alone.
- Rate limiting: hammering `/s/{slug}/answer` past the throttle returns 429.

---

## 6. Verification

- `php artisan test` — new Phase 4 suites green alongside the existing 56 tests from Phases 1–3.
- Manual walkthrough: publish "Patient Satisfaction" for Demo Cafe (already covered in Phase 3's verification) → open the public link in a fresh browser session → answer the NPS question with a low score → confirm a hidden complaint-detail question appears (logic rule) → finish the survey → confirm the Negative Thank You screen appears (support number/WhatsApp/complaint form, no Google Review button) → reopen the same link in the same browser → confirm nothing resets (already completed) → open in a private/incognito window → confirm a brand-new response starts → in the admin builder, confirm the Questions tab is now locked for that survey with an explanatory banner.

---

## 7. Phase 4 status: DONE (as of this build)

Everything above is implemented and verified: `migrate:fresh --seed` runs clean; 67 Pest tests pass (56 from Phases 1–3 + 11 new); a full manual walkthrough (admin creates + publishes a survey, then a separate curl session plays respondent end-to-end) confirmed the whole loop — question-by-question AJAX saves, a low NPS score correctly producing `sentiment=negative`, the negative Thank You screen never showing a Google Review button, resuming a completed link without creating a duplicate response, and the version-lock banner appearing on the builder once the survey had a response.

Three real bugs were caught during this build - two by the Pest suite (not the plan itself), one by manual verification:

- **`SurveyResponseController::show()`'s declared return type didn't match what it actually returned.** It was typed `View|RedirectResponse`, but every branch actually returns `response()->view(...)`, which is `Illuminate\Http\Response`. PHP's return-type enforcement caught this immediately as a fatal error on the very first test - meaning the public survey page would have 500'd on literally every real visit if this had shipped. Fixed by typing it correctly.
- **Radio/Dropdown answer validation was silently checking an empty options list.** `RadioQuestionType`/`DropdownQuestionType::validationRules()` (Phase 2) read `$settings['options']`, but a question's options live in their own `options` column, not `settings` — nobody had ever actually called these methods until this phase. The result: every radio/dropdown answer failed validation against an empty `in:` allow-list, so those questions could never actually be answered. Fixed in `ResponseService::saveAnswer()` by merging `$question->options` into the settings array before validating/scoring.
- **`ResponseService::answersMap()` used `pluck()` on the answers query, which bypasses Eloquent's attribute casting.** The `answer` column is cast to `array` (really: JSON) on the model, but `pluck()` reads the raw column value straight from the query builder without hydrating a model, so every answer arrived at `LogicEngine::evaluate()` as an undecoded JSON string (`'"Yes"'` instead of `'Yes'`) - breaking every string comparison. Fixed by using `get()->mapWithKeys()` instead, which hydrates real models and applies the cast.
- **Testing note, not a bug**: verifying the resume-cookie round-trip requires `TestResponse::getCookie($name)` (auto-decrypts) paired with `withCookie()` (auto-re-encrypts for the next request) - not `withUnencryptedCookie()`, which sends a raw value the app's `EncryptCookies` middleware then fails to decrypt, silently producing a fresh response instead of resuming. Worth remembering for any future test that round-trips an encrypted cookie.
