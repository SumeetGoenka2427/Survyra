# Master Pending-Work Plan

## Purpose

A single index of everything still outstanding across this project, in the order it will be executed. Each item links back to the detailed plan doc that already covers its architecture/scope where one exists; items with no prior doc get enough detail here to build from directly. This doc gets a status update after each stage, the same way `plan/phase-N-*.md` files get a "status: DONE" section appended after building.

Nothing here changes existing business logic unless explicitly stated (Phase 7 is the one exception — it adds new functionality, not just UI).

---

## Track A — UI Modernization (`plan/redesign-plan.md`)

| Stage | Scope | Status |
|---|---|---|
| 0 | Design system foundation (palette, cards/buttons/tables, sidebar+header, toasts, dark-mode toggle, global search) | ✅ DONE |
| 1 | Dashboard (KPI trend, AJAX-refreshable recent clients) | ✅ DONE |
| 2 | Clients — stat strip, AJAX search/filter/pagination, AJAX status toggle/delete | ✅ DONE |
| 3 | Surveys list — card-based list, AJAX publish/archive/delete | ✅ DONE |
| 4 | Templates — category cards, AJAX search/duplicate/delete, usage/creator/updated metadata (preview links already shipped separately). Grid/list toggle deferred — one well-designed card grid covers the need without duplicating markup for marginal benefit. | ✅ DONE |
| 5 | Themes — gallery search/filter, AJAX delete, "in use" survey count (preview links already shipped separately) | ✅ DONE |
| 6 | Campaigns — progress/performance cards (recipient/sent/failed progress bar), AJAX client filter/pagination/send/retry | ✅ DONE |
| 7 | Analytics/Reports — visual polish. KPI cards/tables/cards already inherited Stage 0's global CSS automatically (verified); chart colors/gradient retheme to the indigo/emerald/rose brand palette applied to ApexCharts. | ✅ DONE |
| 8 | Auth pages, error pages, command palette, keyboard shortcuts, dark-mode pass | ✅ DONE |

## Stage 8 — what shipped

1. **Auth pages** — `guest-layout.blade.php` now links `design-system.css` (was still on the near-empty original `app.css` — the one layout that never inherited Stage 0), plus a branded gradient shell and a per-page icon/heading (shield-lock for admin login, person-badge for portal login, envelope for forgot-password, key for reset). Confirmed no Register page is needed — this app has no public self-registration by design.
2. **Error pages** — `resources/views/errors/{403,404,500}.blade.php` added using `x-ds-empty-state` inside `x-guest-layout`; verified a real 404 now renders the branded empty state instead of Laravel's bare default.
3. **Command palette** — the header search input is now a read-only trigger; focusing/clicking it (or `Ctrl+K` anywhere) opens a real centered modal (`#command-palette-overlay`) with arrow-key/Enter navigation over the same `admin.search` endpoint, `Esc` or backdrop-click to close.
4. **Keyboard shortcuts** — `Ctrl+S` submits the form containing the focused element (falling back to the first form in `<main>`); `Esc`-closes-modal needed no new code since Bootstrap 5's modal component already does this natively.
5. **Dark-mode completeness** — fixed three hardcoded `#fff` backgrounds in `design-system.css` (toast, command palette, search-input focus state) that would have shown a bright-white popup against a dark page; sidebar/topbar/cards/tables were already theme-aware from Stage 0.

Verified: all 112 tests still green, real HTTP walkthrough of login/404/dashboard/command-palette markup/search endpoint, dev DB reset after.

## Track B — Survey Design Modernization (`plan/survey-design-modernization-plan.md`)

| Item | Scope | Status |
|---|---|---|
| Question styles (8 types, 2-4 looks each) + 2 layouts | NPS/Rating/Emoji/Radio/Checkbox/YesNo/Textbox/Textarea styles, Multi-step + Conversational layouts | ✅ DONE |
| One-page layout | Built — see status below | ✅ DONE |
| Card-based layout | Built — see status below | ✅ DONE |
| Section-Wizard layout | Built — see status below | ✅ DONE |
| CSAT/CES/Dropdown/Number/Email/Phone/Date multi-style expansion | Currently one modernized "default" look each | ✅ DONE |
| Matrix / Ranking / Slider question types | **Net-new question types, not a redesign** — new `QuestionTypeContract` implementations with real scoring/validation, DB `question_types` rows, builder settings-panel support | ✅ DONE |
| `admin.survey-preview` layout-awareness | Preview always rendered the old single-question stepper regardless of the survey/template's actual layout | ✅ DONE |

**One-page layout — status: DONE.** Built exactly as option (b) recommended above: `ResponseService::visibleQuestions()` reuses the existing `LogicEngine`/`hiddenQuestionIds()` as-is (one engine, no JS reimplementation of rule evaluation) to compute every currently-applicable question; each question renders as its own independently-autosaving mini-form (reuses every existing per-question-type render partial completely unchanged - no touching the 23 style variants from the survey-design-modernization pass); a single "Submit Survey" button calls the existing `/submit` endpoint. The `/answer` AJAX response now also returns a `questionIds` array alongside the HTML fragment, so the frontend only re-renders the question list when a logic rule actually changed which questions apply (not on every keystroke) - avoiding the "full-page AJAX re-render on every field" feel this note originally worried about. A new `assertRequiredQuestionsAnswered()` check in `ResponseService::submit()` (a no-op safety net for multi-step/conversational, which already force completeness structurally) enforces that every required-and-visible question has an answer before a one-page survey can submit, returning a 422 otherwise.

**Known v1 limitation, flagged rather than silently accepted**: answers aren't visually pre-filled back into their fields if a question is hidden then re-shown, or if the respondent reloads the page mid-way - the previously-saved value stays safely in the database (autosave never re-submits a blank over it unless the respondent actively clears the field), but the input box itself shows empty until they re-type. Pre-filling would require every one of the 23 style-variant Blade partials to accept and render an existing value, which is real additional work - deferred rather than rushed.

Verified via 5 new Pest tests (question-set filtering, autosave response shape, conditional reveal, submit-blocked-when-incomplete, submit-succeeds-when-complete) plus a full manual walkthrough (logic-gated question appeared after its trigger was answered, optional question correctly didn't block submission, response completed correctly) - 123 tests passing total.

**Card-based layout — status: DONE.** Exactly the "visual variant" this table predicted: `SurveyResponseController` now has a `usesAllQuestionsLayout()`/`allQuestionsView()` pair so `one_page` and `card_based` share 100% of the controller/service logic, differing only in which Blade partial renders the list (`_card-based-questions.blade.php`: each question in its own numbered, top-accented card with hover-lift, vs one-page's plain bordered list). 2 new tests, 125 passing.

**Section-Wizard layout — status: DONE.** Groups visible questions into fixed-size steps (`ResponseService::currentSection()`, 3 questions per step) and auto-advances once every required question in the current step is answered - "current step" is derived fresh each request from (visible questions, answered ids), the same stateless approach `nextQuestion()` already used for single questions, so there's no new stored position to keep in sync. Reuses the *exact* one-page autosave/submit engine and DOM contract (`#one-page-list`, `.one-page-answer-form`, `#one-page-submit`) end to end - the JS needed zero new logic beyond adding `section_wizard` to the layout check, since a section auto-advancing IS just "the visible question set changed" from the frontend's point of view. Ships with the same "no back button" limitation multi-step already has today (not a new gap - going back would need an explicit stored position rather than a derived one). 4 new tests, 129 passing. Manually verified the full 5-question/2-section flow: section 1 showed exactly 3 questions, answering all 3 auto-advanced to section 2 with the Submit button appearing only there, submitting early 422'd, submitting complete succeeded.

## Track C — Original Platform Roadmap (`plan/iterative-strolling-flamingo.md`)

| Phase | Scope | Status |
|---|---|---|
| 1-6 | Foundation → Analytics/Reporting | ✅ DONE |
| 7 | **Reputation Management — review-click analytics + Module 12 Notifications.** Built exactly per `plan/phase-7-reputation-management.md`. Along the way, found and fixed: `portal-layout.blade.php` had never actually received Stage 0's redesign (despite Track A claiming it had "lighter-touch polish") — now fixed. 118 tests passing (+6). | ✅ DONE |
| 8 | Optimization & Future (AI, public API, white-label, billing) | explicitly out of scope until asked |

---

## `admin.survey-preview` layout-awareness — status: DONE

`SurveyPreviewController::index()` now resolves `$layout` from the survey/template (falling back to `multi_step`) and groups the question list into "steps" that match how each real layout paces a respondent — one question per step (multi_step/conversational), fixed 3-question groups per step (section_wizard, mirroring `ResponseService::QUESTIONS_PER_SECTION`), or a single step holding every question (one_page/card_based). The Blade view loops over these step groups instead of raw questions, applying the card-based numbered-card treatment when appropriate — the existing client-side `data-step`/`data-preview-next` stepper JS needed zero changes since chunking already produces the same one-div-per-step shape it expected. Verified with 7 new Pest tests covering all 5 layouts plus the theme-only fallback, and manually via curl against a real seeded survey (Matrix question rendered correctly on its own step with the `table` style).

## CSAT/CES/Dropdown/Number/Email/Phone/Date multi-style expansion — status: DONE

Same pattern as the original 8-type expansion: each type's `availableStyles()`/`renderComponent()` now point at a `survey-questions/{key}/*.blade.php` directory instead of a flat single file. CSAT/CES got `numbers`/`circles`/`gradient` (reusing the exact NPS scale-row markup at their own min/max defaults); Dropdown got `select`/`buttons`/`pills` (buttons/pills reuse radio's exact partials since a single-choice dropdown and single-choice radio are the same underlying interaction); Number/Email/Phone got `modern`/`floating` (mirroring Textbox's existing two-style pattern exactly); Date got `modern`/`labeled` — a static label instead of a floating one, a deliberate deviation since `:placeholder-shown` (which the floating-label CSS relies on) doesn't reliably fire for `<input type="date">` across browsers. The builder settings-panel component (`question-type-fields.blade.php`) needed zero changes — it already read `availableStyles()` generically. `resolveStyle()`'s existing fallback-to-first-style behavior means every survey/template with the old stored `'default'` style value keeps rendering identically post-upgrade. Verified with 7 new Pest tests (fallback + resolution + live render-through-survey checks) plus the full existing suite staying green — 143 passing.

## Matrix / Ranking / Slider question types — status: DONE

Three new `QuestionTypeContract` implementations, one config line and one seeder row each — no changes to `QuestionTypeRegistry` or any other type's code, confirming the extensibility promise actually holds.

- **Matrix** (`builderGroup() = 'matrix'`, a new group added to the builder UI showing both the rows textarea and the scale min/max/label fields together): rows come from the existing `options` column (reusing `options_text` parsing unchanged), columns are a numeric scale from `settings['scale_min']` to `scale_max`. Two styles — `table` (grid) and `stacked` (one scale row per statement, more mobile-friendly). Answer is a JSON object keyed by row index; `score()` returns the average of every row's rating so matrix questions still feed a single number into reports. Validation is a closure (not `Rule::forEach`, which only applies to `field.*` wildcard keys and doesn't fit this codebase's single-key `['answer' => $rules]` validation call) checking every row is within range, plus a `size:` check enforcing all rows answered when required.
- **Ranking** (`builderGroup() = 'choice'`, no builder UI changes needed — items are just the existing options list): a single style, up/down arrow reordering rather than drag-and-drop, deliberately kept to one well-tested interaction rather than stretching to a second, weaker one (e.g. rank-via-dropdown can't structurally guarantee a valid permutation without extra JS). `survey.js` grew a small reorder handler scoped to `[data-ranking-*]` elements plus a dispatched synthetic `change` event so the existing one-page/card-based/section-wizard autosave (which listens for native `change` bubbling) picks up reorders — without that dispatch, reordering would never persist outside the multi-step/conversational explicit-submit layouts. Server-side validation is a closure confirming the submitted array is an exact permutation of the configured items.
- **Slider** (`builderGroup() = 'scale'`, reuses the existing scale min/max/label fields as-is): `range` style is a native `<input type="range">` with a live numeric readout via a self-contained inline `oninput` handler (no `survey.js` changes needed); `buttons` style reuses the NPS "numbers" row markup verbatim. Added to `SurveyService::SCORABLE_TYPES` so a slider question can be auto-picked as a survey's primary scoring question, same as NPS/CSAT/CES/rating types.

Verified with 12 new Pest tests (style resolution, full render-and-submit-through-a-published-survey for each type, required/permutation/range validation, scoring, primary-score auto-pick, and a real HTTP round-trip through the template builder's add-question endpoint) plus the full suite staying green — 155 passing.

## Execution order from here

Track A (Stages 0-8), Track C Phase 7, and Track B (all layouts, all style expansions, the 3 net-new question types, and the survey-preview fix) are now fully complete. Everything explicitly listed in the original master plan except the deliberately-deferred answer-prefill limitation (one-page/card-based/section-wizard don't visually restore saved values into fields on reload — noted above, real additional work across 23+ style partials, deferred rather than rushed) is done.

Nothing is currently pending. Demo content: `database/seeders/DemoClientsSeeder.php` seeds 5 realistic clients (Healthcare, Restaurant, Customer Support, Education, Retail) each with a real published survey, theme, ~30-50 contacts, one completed campaign, and 40-65 scored/sentiment-tagged responses (including live negative-feedback notifications and review-click rows) — see `plan/demo-clients-seeder.md` for the full breakdown.

Each stage: build → `php artisan test` stays green → manual curl/browser walkthrough → reset dev DB → append a status note here before moving to the next.
