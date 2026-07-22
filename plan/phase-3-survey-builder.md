# Phase 3 — Survey Builder

## Context

Phases 1 (Foundation) and 2 (Template Library) are done: dual-guard auth, Client Management, and a library of 10 templates/7 themes/15 question types that Survyra Admin can edit but which no client can see. Phase 3 is where those templates become real, live surveys attached to a specific client — Module 5 (Survey Builder) and Module 6 (Survey Publishing) in `task.md`, following the exact workflow in §9: **Choose Template → Customize Questions → Select Theme → Configure Logic → Configure Thank You Rules → Publish → Generate Public Link → Generate QR → Ready to Share.**

**Correction to the master roadmap**: the master plan (`plan/iterative-strolling-flamingo.md`) originally placed the Thank You Engine (`survey_thankyou_rules`) in Phase 7. That contradicts the blueprint's own §9 workflow, which lists "Configure Thank You Rules" as a builder step *before* Publish — a survey shouldn't be publishable without it, since §12 calls the Thank You Engine "a core feature" and the "never redirect unhappy customers to Google Reviews" rule has to exist before anything goes live. The master roadmap table has been updated: Phase 3 now owns `survey_thankyou_rules`; Phase 7 keeps only the surrounding reputation-management features (negative-feedback alerts wired to Notifications, review-click analytics) that build *on top of* the rules configured here.

**Out of scope for this phase, deliberately**: the public survey-taking page (`/s/{slug}`), AJAX autosave, and live logic-rule evaluation against real answers are all Phase 4 (Response Engine). Phase 3 builds the `LogicEngine` evaluation service and unit-tests it against sample answer arrays, but nothing calls it from a live page yet — same pattern as Phase 2's `QuestionTypeContract.renderComponent()`, declared and ready, consumed later.

---

## 1. Scope (from `task.md` §5, §6, §9, §11, §12)

- Admin picks a **Client** + a **Template**, which clones the template's questions into a new draft **Survey**.
- Admin can then **customize** that survey's questions independently of the template (add/edit/remove/reorder) — editing a survey never touches the template it came from.
- Admin **selects a theme** for the survey (a system theme, an existing client-custom theme, or duplicates a system theme into a new client-custom one to tweak).
- Admin configures **conditional logic** rules (IF question OPERATOR value [AND ...] THEN show/hide another question).
- Admin configures the **3 Thank You rules** (positive/neutral/negative), auto-seeded with sensible defaults matching §12's exact examples when the survey is created.
- Admin **publishes** the survey — generates a short public slug and a downloadable QR code pointing at it.

---

## 2. Database

**New tables** (already named in the master schema §2, filled in here):
- `surveys` — client_id, survey_template_id (nullable), title, slug, version, theme_id, status (draft/published/archived), settings (json), published_at, created_by, soft deletes.
- `survey_questions` — survey_id, question_type_id, question_text, options (json), settings (json), order, is_required. Same shape as Phase 2's `survey_template_questions` — cloned from there at survey-creation time, then independent.
- `survey_logic_rules` — survey_id, source_question_id, conditions (json), action, target_question_id, priority.
- `survey_thankyou_rules` — survey_id, sentiment, min_score, max_score, thank_you_message, show_google_review, show_facebook, show_instagram, show_website, show_coupon, coupon_code, show_complaint_form, show_support_number, show_whatsapp_button, manager_contact (json).

**Schema addition beyond the master plan**: `surveys.primary_score_question_id` (nullable FK to `survey_questions`) — needed to implement gap #7's resolution ("each survey designates one primary scoring question"). Auto-set at creation to the first NPS/CSAT/CES/Rating question found among the cloned questions (or the first question overall if none of those types exist); admin can change it later. Changing it does **not** retroactively change the thank-you rule score boundaries the admin already configured — the two are decoupled on purpose.

**Simplifications vs. the master schema sketch**:
- `survey_logic_rules.action` is just `show` / `hide` — the blueprint's "skip" example (*"If Rating >= 4 → Skip Complaint Questions"*) is semantically identical to hiding those questions, so a separate `skip_to` jump-target action isn't needed and isn't built.
- `target_section_id` is dropped — nothing else in the schema has a "section" concept yet, so it'd be dead weight. Revisit if/when survey sections become a real feature.
- `survey_thankyou_rules.priority` is dropped — there are exactly 3 rows per survey (one per sentiment, enforced by a `unique(survey_id, sentiment)` constraint), so there's nothing to prioritize between.

---

## 3. Architecture decisions

- **Conditions are a flat AND list for v1, not nested AND/OR groups.** The blueprint asks for "unlimited nested conditions," but that's a lot of UI complexity for a first pass. `conditions` is stored as a JSON array of `{question_id, operator, value}` objects, ANDed together — the schema itself doesn't prevent nesting later (a condition could become a group later without a migration), but the builder UI only exposes a flat list now. Flagging this as a scoped-down v1, not a forgotten requirement.
- **`LogicEngine` service** (`app/Services/LogicEngine.php`): `evaluate(SurveyLogicRule $rule, array $answers): bool`, checking every condition's operator (`equals`, `not_equals`, `contains`, `greater_than`, `less_than`, `is_empty`, `is_not_empty`) against the provided answers array. Pure function, no DB/session dependency — unit-testable now with hand-built answer arrays, wired into the live survey page in Phase 4.
- **Thank-you rules are auto-seeded, not built from a blank slate.** When a survey is created, 3 `survey_thankyou_rules` rows are created immediately (sentiment: positive/neutral/negative) with messages and toggles copied verbatim from §12's examples: Positive → thank-you message + Google/Facebook/website buttons; Neutral → thank-you message only, no review ask; Negative → apology message + complaint form + support number + WhatsApp button, **`show_google_review` hard-locked to `false`**. This means every survey is safely publishable from the moment it's created, and admin customization is optional polish, not a blocking prerequisite.
- **The negative-sentiment safety rule is enforced server-side, not just hidden in the UI.** `UpdateThankyouRuleRequest` rejects `show_google_review = true` when `sentiment = negative` outright (422, not a silent no-op) — "never redirect unhappy customers to Google Reviews" is a data-layer invariant, not a UI suggestion.
- **Theme assignment reuses Phase 2's Theme Library instead of building a second theme editor.** The builder's "Select Theme" step offers a dropdown (system themes + this client's existing custom themes) plus a **"Duplicate as custom theme for this client"** button — mirrors the "Save as New Template" pattern from Phase 2. Duplicating creates a new `client_id`-scoped `survey_themes` row and redirects into Phase 2's existing theme edit page to tweak it, with a "back to survey" link. No new theme-editing UI is built here.
- **Public link is a random short slug, not the template/title text and not a raw UUID** — resolves master gap #8 concretely. An 8-character random alphanumeric string, generated at publish time, unique across `surveys`. Short enough for SMS, doesn't leak the client's name or let someone guess adjacent surveys by editing a title-based slug.
- **QR generation in this phase is ad-hoc, not persisted.** Installs `simplesoftwareio/simple-qrcode` (already named in the original tech stack, just not yet installed) and adds a "Download QR" button that streams a PNG on demand, encoding the public link. No `qr_codes` table row is created. The richer, persisted, multi-format (PNG/SVG/PDF), labeled QR system ("Table 5", "Reception Desk") described in §17 is Phase 5's Campaign Manager work, which needs that persistence for tracking/reprinting — this phase just needs "here's a QR so I can share this survey right now."
- **Version-lock enforcement is deferred to Phase 4.** The master plan's gap #4 called for locking structural edits once a survey has ≥1 response. The `surveys.version` column is created now, but the actual guard (`canEditStructure(): bool`, checking `$survey->responses()->count()`) can't be built until the `responses` table exists — that's Phase 4. Documented here so it isn't mistaken for an oversight.
- **A survey can only be deleted while in `draft` status** (mirrors the version-lock spirit without needing the `responses` table yet — a published survey might already have a shared link out in the world). Archiving (`status = archived`) is available for published surveys instead of deletion.

---

## 4. Implementation breakdown

**Models**: `Survey` (belongsTo `Client`, `SurveyTemplate` nullable, `SurveyTheme`, `User` as `createdBy`, `SurveyQuestion` as `primaryScoreQuestion`; hasMany `SurveyQuestion`, `SurveyLogicRule`, `SurveyThankyouRule`), `SurveyQuestion` (belongsTo `Survey`, `QuestionType` — same shape as Phase 2's `SurveyTemplateQuestion`), `SurveyLogicRule` (belongsTo `Survey`, source/target `SurveyQuestion`), `SurveyThankyouRule` (belongsTo `Survey`).

**Services**:
- `SurveyService` — `createFromTemplate(Client, SurveyTemplate, title, createdByUserId)` (clones questions, auto-picks primary score question, auto-seeds the 3 thank-you rules), `update`, `publish` (validates ≥1 question, generates the short slug, sets `published_at`), `archive`, `delete` (draft-only guard), plus the same `addQuestion`/`updateQuestion`/`removeQuestion`/`moveQuestionUp`/`moveQuestionDown` pattern as Phase 2's `SurveyTemplateService` (question ordering logic is identical — consider extracting a shared trait/base if it turns out to be verbatim duplication once both exist side by side).
- `SurveyLogicRuleService` — create/update/delete for `survey_logic_rules`.
- `SurveyThankyouRuleService` — `updateForSentiment(Survey, string $sentiment, array $data)` (upsert-by-sentiment, enforces the negative/Google-review lock at the service layer as a second line of defense behind the Form Request).
- `LogicEngine` — the condition-evaluation service described above.
- `SurveyThemeService` gains one method: `duplicateForClient(SurveyTheme $theme, int $clientId): SurveyTheme`.

**Repositories**: `SurveyRepositoryInterface` + `SurveyRepository` (same pattern as Phase 1/2 — `paginate`/`find`/`create`/`update`/`delete`, plus `forClient(int $clientId)`), bound in the existing `RepositoryServiceProvider`.

**Form Requests**: `StoreSurveyRequest` (client_id, survey_template_id, title), `UpdateSurveyRequest` (title, theme_id), `StoreSurveyQuestionRequest`/`UpdateSurveyQuestionRequest` (identical shape to Phase 2's template-question request, including the newline-textarea options parsing), `StoreSurveyLogicRuleRequest` (source_question_id, conditions array, action, target_question_id), `UpdateThankyouRuleRequest` (sentiment-aware — rejects `show_google_review=true` when sentiment is `negative`).

**Policies**: `SurveyPolicy`, `SurveyLogicRulePolicy`, `SurveyThankyouRulePolicy` — all gate on the same `manage-surveys` permission Phase 2 already seeded.

**Controllers** (`App\Http\Controllers\Admin\*`, same namespacing convention Phase 2 introduced):
- `SurveyController` — index (list, filterable by client), create (client + template picker), store, edit (the tabbed builder page), update, `publish`, `archive`, destroy, `downloadQr`.
- `SurveyQuestionController` — store/update/destroy/moveUp/moveDown/`setPrimaryScore`, nested under a survey (same `assertBelongsToSurvey` guard pattern as Phase 2's `assertBelongsToTemplate`).
- `SurveyLogicRuleController` — store/update/destroy, nested under a survey.
- `SurveyThankyouRuleController` — single `update(Survey, string $sentiment)` action (no create/delete — the 3 rows always exist).
- `SurveyThemeController` (Phase 2, extended) — new `duplicateForClient(SurveyTheme, Survey)` action.

**Routes** (added to `routes/admin.php`, inside the existing `permission:manage-surveys` group):
```
GET|POST   /admin/surveys[/create]
PUT|DELETE /admin/surveys/{survey}[...]
POST       /admin/surveys/{survey}/publish
POST       /admin/surveys/{survey}/archive
GET        /admin/surveys/{survey}/qr

POST       /admin/surveys/{survey}/questions
PUT|DELETE /admin/surveys/{survey}/questions/{question}
PATCH      /admin/surveys/{survey}/questions/{question}/move-up|move-down|set-primary-score

POST       /admin/surveys/{survey}/logic-rules
PUT|DELETE /admin/surveys/{survey}/logic-rules/{rule}

PUT        /admin/surveys/{survey}/thankyou-rules/{sentiment}

POST       /admin/surveys/{survey}/theme/{theme}/duplicate
```

**Views**:
- `admin/surveys/index.blade.php` — list with client/status filters.
- `admin/surveys/create.blade.php` — Client dropdown, Template dropdown (grouped by industry, reusing Phase 2's grouped-template data), Title (defaults to the template's name, editable).
- `admin/surveys/edit.blade.php` — Bootstrap nav-tabs shell with 5 panes: **Questions** (same builder pattern as Phase 2's template edit — reuses `components/question-type-fields.blade.php` unchanged, plus a "Use as primary score" radio next to each question), **Logic** (rule list + add-rule form with an Alpine-repeatable condition row), **Theme** (dropdown + duplicate-for-client button), **Thank You** (3 fixed cards: Positive/Neutral/Negative, each with the message textarea and its toggles — the Negative card's Google Review toggle is rendered disabled, not just unchecked), **Publish** (status, public link once published, "Download QR" button).

**Seeders**: none new — surveys are created through the UI per-client, not seeded demo data (unlike templates/themes, which are shared library content).

---

## 5. Tests (Pest)

- Creating a survey from a template clones every question (type, text, options, settings, order) and auto-seeds exactly 3 thank-you rules with the right defaults per sentiment.
- Editing a cloned survey's questions never touches the source template's questions (and vice versa).
- `LogicEngine::evaluate()` unit tests for each operator (`equals`, `not_equals`, `contains`, `greater_than`, `less_than`, `is_empty`, `is_not_empty`) and for a multi-condition AND rule, using plain answer arrays — no HTTP, no DB rows beyond the rule itself.
- Attempting to set `show_google_review = true` on the negative thank-you rule is rejected (422) both at the Form Request and, redundantly, at the service layer.
- Publishing a survey with zero questions fails; publishing a valid survey sets `status`, `published_at`, and a unique 8-character `slug`.
- A draft survey can be deleted; a published survey cannot (must be archived instead).
- Duplicating a theme "for this client" creates a new `survey_themes` row with `client_id` set and assigns it to the survey.
- Downloading the QR code for a published survey returns an image response (`image/svg+xml` - see §7 for why SVG, not PNG); attempting it for a draft survey is rejected.
- Permission/guard isolation tests mirroring Phase 2's pattern: a user without `manage-surveys` is forbidden everywhere above; a `client` guard user is redirected away from every `/admin/surveys*` route.

---

## 6. Verification

- `php artisan test` — new Phase 3 suites green alongside the existing 39 tests from Phases 1–2.
- Manual walkthrough as Survyra Admin: New Survey → Demo Cafe + "Patient Satisfaction" template → builder loads with the 5 cloned questions → add a Dropdown question → mark the NPS question as the primary score → add a logic rule ("IF wait-time-radio equals 'No' THEN show the textarea") → Theme tab → duplicate "Cafe" as a custom theme for Demo Cafe, tweak its primary color in the Phase 2 theme editor, confirm it's now selected on the survey → Thank You tab → confirm all 3 cards are pre-filled, confirm the Negative card's Google Review toggle is disabled, edit the Positive message → Publish → confirm the public link and slug appear and "Download QR" returns a scannable code.

---

## 7. Phase 3 status: DONE (as of this build)

Everything above is implemented and verified: `migrate:fresh --seed` runs clean; 56 Pest tests pass (39 from Phases 1–2 + 17 new); a full manual HTTP walkthrough confirmed survey creation from a template, the auto-seeded thank-you rules (including the hard-locked negative Google Review flag), publish (status/slug/published_at), and QR download.

Deviations/notes from the build:

- **QR codes are SVG, not PNG.** `simplesoftwareio/simple-qrcode`'s PNG backend requires the `imagick` PHP extension, which isn't installed on this machine (and isn't a one-line install on Windows/XAMPP - it needs a prebuilt DLL matched to the exact PHP build). SVG needs no such dependency, scales cleanly for print, and is arguably a better fit for the table-tent/poster QR use cases in `task.md` §17 anyway. `downloadQr()` now returns `image/svg+xml`.
- **`source_question_id` on a logic rule is derived, not admin-entered.** Rather than asking the admin to redundantly pick "which question is this rule about" separately from the conditions list, `StoreSurveyLogicRuleRequest` derives it from `conditions[0].question_id` in `prepareForValidation()`. One less field in the builder UI; the column still exists for Phase 4's per-question rule lookup.
- **Extracted a `ReordersQuestions` trait** (`app/Services/Concerns/ReordersQuestions.php`) from the move-up/move-down-by-swapping-order logic that was duplicated verbatim between Phase 2's `SurveyTemplateService` and this phase's `SurveyService`. Refactored `SurveyTemplateService` to use it too; all Phase 2 tests still pass unchanged.
- **Logic rule editing is delete-and-recreate, not in-place edit, in the UI** — the `update` endpoint and Form Request exist and work (reachable directly, e.g. by API), but the builder's Logic tab only exposes Add and Delete buttons. Given rules are cheap to recreate and the flat-AND builder form is already on screen, a dedicated inline-edit UI didn't seem worth the added complexity for v1.
