# Phase 2 — Template Library

## Context

Phase 1 (Foundation) is done: dual-guard auth, Client Management CRUD, role-aware dashboards. Per the master plan (`plan/iterative-strolling-flamingo.md`), Phase 2 builds the **Survey Template Library** — Module 4 in `task.md` — which everything downstream depends on: the Survey Builder (Phase 3) clones a template into a client's survey, and the question-type architecture built here is what lets Phase 3/4 render and score answers without ever touching this phase's code again.

This phase is Survyra-Admin-only tooling. Clients never see templates — they only ever see the surveys built *from* them (Phase 3+).

---

## 1. Scope (from `task.md` §8, §10, §13)

- **Question type registry** — 15 types (NPS, CSAT, CES, Radio, Checkbox, Dropdown, Textbox, Textarea, Number, Email, Phone, Date, Rating Stars, Emoji Rating, Yes/No), extensible without touching existing code (dev rule from §10).
- **10 seeded survey templates** across the 5 target industries (§8), each editable: add/remove/reorder questions, change options, "Save as New Template".
- **7 seeded survey themes** (§13): Healthcare, Corporate, Cafe, Luxury, Minimal, Dark, Modern — each carrying logo/colors/background/button style/font/progress bar/border radius/custom CSS.

---

## 2. Database (already designed in the master plan §2 — no new tables needed here)

- `question_types` — key, label, scoring_type, settings_schema (json), is_active.
- `survey_templates` — name, industry_category, description, is_active, created_by.
- `survey_template_questions` — survey_template_id, question_type_id, question_text, options (json), settings (json), order, is_required.
- `survey_themes` — name, is_system, client_id (nullable), logo_path, primary_color, secondary_color, background, button_style, font, progress_bar_style, border_radius, custom_css.

Migrations for all four already exist as stub files from the master schema pass — this phase fills them in and seeds them.

---

## 3. Architecture decisions for this phase

- **`QuestionTypeContract` interface** (`app/Contracts/QuestionTypeContract.php`): `key()`, `label()`, `scoringType()`, `validationRules(array $settings, bool $required): array`, `score(mixed $answer, array $settings): ?float`, `builderComponent(): string` (Blade component used in the template/question builder UI), `renderComponent(): string` (used later, Phase 4, to render the question on the public survey page). Declaring `renderComponent()` now — even though nothing calls it until Phase 4 — is what makes "add a new type without touching existing code" true later: the contract is the seam.
- **One class per type**, `app/QuestionTypes/{Nps,Csat,Ces,Radio,Checkbox,Dropdown,Textbox,Textarea,Number,Email,Phone,Date,RatingStars,EmojiRating,YesNo}QuestionType.php`, each implementing the contract. Registered in `config/question_types.php` as `'nps' => NpsQuestionType::class, ...`. A new type in the future = one class + one config line + one `question_types` row — no edits to the other 14.
- **Options stored as a plain newline-delimited textarea**, not a dynamic add/remove-row JS widget. For choice-based types (Radio, Checkbox, Dropdown) the builder shows a `<textarea>` — one option per line — parsed server-side into a JSON array on save. Simpler than a JS option-row builder and sufficient for MVP; can upgrade to a richer widget later if template authors complain.
- **Reordering via move-up/move-down buttons**, not drag-and-drop. Avoids pulling in a new JS dependency (e.g. SortableJS) beyond what the stack already lists (Bootstrap 5 + Alpine + AJAX). Each click is a plain AJAX PATCH that swaps `order` with the adjacent row.
- **Alpine.js is added to the admin layout in this phase** (`resources/views/components/admin-layout.blade.php` gets the Alpine CDN `<script>` — Phase 1 didn't need it). Used for: showing/hiding the right settings fields when the question-type dropdown changes, and the inline "Add Question" panel.
- **Themes are a shared library, not yet client-specific.** `survey_themes.client_id` exists in the schema for a future per-client override, but Phase 2 only seeds the 7 system themes (`is_system = true`, `client_id = null`) and lets Survyra Admin manage that library. Attaching/customizing a theme *for a specific client's survey* happens in Phase 3 (§9 workflow: "Select Theme" step), not here.
- **Permission gate**: reuse the `manage-surveys` permission already seeded in Phase 1's `RoleSeeder` (assigned to both `super_admin` and `survyra_admin`) — no new permission needed. Routes use `permission:manage-surveys` middleware, demonstrating the permission-based gate alongside Phase 1's role-based one.

---

## 4. Implementation breakdown

**Models**: `QuestionType`, `SurveyTemplate` (hasMany `SurveyTemplateQuestion`, belongsTo `User` as `createdBy`), `SurveyTemplateQuestion` (belongsTo `SurveyTemplate`, belongsTo `QuestionType`), `SurveyTheme`.

**Contracts & registry**: `app/Contracts/QuestionTypeContract.php`, the 15 `app/QuestionTypes/*QuestionType.php` classes, `config/question_types.php` registry map, a small `QuestionTypeRegistry` service (`app/Services/QuestionTypeRegistry.php`) that resolves a `question_types.key` to its class instance — this is what controllers/views call instead of instantiating classes directly.

**Repositories/Services** (same pattern as Phase 1's `ClientRepository`/`ClientService`): `SurveyTemplateRepositoryInterface` + `SurveyTemplateRepository`, `SurveyTemplateService` (create/update/duplicate/reorder-question/add-question/remove-question), `SurveyThemeService` (simpler — thin CRUD, no separate repository needed since there's no complex query logic).

**Form Requests**: `StoreSurveyTemplateRequest`/`UpdateSurveyTemplateRequest` (name, industry_category, description), `StoreTemplateQuestionRequest` (question_type_id, question_text, is_required, options as newline text → array, settings per type), `StoreSurveyThemeRequest`/`UpdateSurveyThemeRequest` (name, colors, font, button_style, background, progress_bar_style, border_radius, custom_css).

**Policies**: `SurveyTemplatePolicy`, `SurveyThemePolicy` — both gate on the `manage-surveys` permission (mirrors `ClientPolicy`'s role checks from Phase 1, but permission-based).

**Controllers**:
- `Admin\SurveyTemplateController` — index (grouped by industry_category), create, store, edit (metadata + questions builder), update, destroy, `duplicate` ("Save as New Template").
- `Admin\SurveyTemplateQuestionController` — nested under a template: store, update, destroy, `moveUp`, `moveDown`.
- `Admin\SurveyThemeController` — standard resource: index (grid of preview cards), create, store, edit, update, destroy.

**Routes** (added to `routes/admin.php`, inside the existing `auth:web` + `permission:manage-surveys` group):
```
GET|POST   /admin/templates[/create]
PUT|DELETE /admin/templates/{template}[...]
POST       /admin/templates/{template}/duplicate
POST       /admin/templates/{template}/questions
PUT|DELETE /admin/templates/{template}/questions/{question}
PATCH      /admin/templates/{template}/questions/{question}/move-up
PATCH      /admin/templates/{template}/questions/{question}/move-down
GET|POST|PUT|DELETE  /admin/themes[...]   (standard resource)
```

**Views**:
- `admin/templates/index.blade.php` — cards grouped by industry, "New Template" button, duplicate/delete actions.
- `admin/templates/create.blade.php` — metadata-only form; on save, redirects into `edit` to build questions.
- `admin/templates/edit.blade.php` — metadata form + question list (move-up/down, edit, delete buttons) + an "Add Question" panel (Alpine-toggled fields depending on selected type).
- `components/question-type-fields.blade.php` — the Alpine-driven per-type settings block (options textarea for choice types; scale min/max + labels for NPS/CSAT/CES/Rating; nothing extra for Email/Phone/Date/Textbox/Textarea/Number/Yes-No beyond the shared required/label fields).
- `admin/themes/index.blade.php` — grid of theme preview cards (mini live preview using inline CSS variables from each theme's colors/font/button style).
- `admin/themes/create.blade.php` / `edit.blade.php` — form with color inputs + the same live preview panel.

**Seeders**:
- `QuestionTypeSeeder` — the 15 types, each with its `scoring_type` (`none`/`scale`/`nps`/`csat`/`ces`/`boolean`) and a minimal `settings_schema`.
- `SurveyThemeSeeder` — the 7 system themes with sensible preset colors/fonts (e.g. Healthcare: calm blues/teal; Luxury: black/gold; Dark: dark background/light text; Minimal: mono, no shadows).
- `SurveyTemplateSeeder` — 10 templates, 2 per industry, each with a representative question mix pulled from the 15 types:
  - **Healthcare**: Patient Satisfaction, Doctor Consultation
  - **Restaurant**: Dining Experience, Delivery Feedback
  - **Customer Support**: NPS Survey, CSAT Survey
  - **Education**: Course Feedback, Student Satisfaction
  - **Retail**: Store Experience, Purchase Feedback

  Worked example (Patient Satisfaction, Healthcare): NPS ("How likely are you to recommend this clinic?"), Rating Stars ("Rate your doctor's consultation"), Radio ("Was the wait time acceptable?" — Yes/No/Somewhat), Textarea ("Any additional comments?"), Yes/No ("Would you visit again?"). The other 9 templates follow the same shape — one primary scoring question (NPS/CSAT/CES/Rating) plus 3–5 supporting questions relevant to that template's context.

All three seeders are called from `DatabaseSeeder` after `DemoDataSeeder`.

---

## 5. Tests (Pest)

- Question type registry resolves all 15 keys to a class implementing `QuestionTypeContract`.
- A `survyra_admin` can create a template, add/edit/remove/reorder its questions, and duplicate it ("Save as New Template" produces a new template row with copied questions, original untouched).
- A `survyra_admin` without `manage-surveys` permission is forbidden from all of the above (mirrors Phase 1's `GuardIsolationTest` pattern).
- Theme CRUD: create/update/delete a custom theme; the 7 system themes exist after seeding and are flagged `is_system = true`.
- Client portal users (`client` guard) get a 302/redirect on every `/admin/templates*` and `/admin/themes*` route — templates are never client-reachable.

---

## 6. Verification

- `php artisan migrate:fresh --seed` — confirms `question_types` (15 rows), `survey_themes` (7 rows), `survey_templates` (10 rows) seed cleanly.
- `php artisan test` — new Phase 2 suites green alongside the existing 28 Phase 1 tests.
- Manual walkthrough as Survyra Admin: open Template Library → edit "Patient Satisfaction" → add a Dropdown question → reorder it above the Textarea → duplicate the template as "Patient Satisfaction (Copy)" → confirm the original is unchanged → open Theme Library → edit "Dark" theme's primary color → confirm the preview card updates.

---

## 7. Phase 2 status: DONE (as of this build)

Everything above is implemented and verified: `migrate:fresh --seed` produces exactly 15 question types, 7 themes, 10 templates (2 per industry, 41 questions total); 39 Pest tests pass (28 from Phase 1 + 11 new); manual curl-driven walkthrough confirmed template index/edit, question add/reorder/duplicate, and theme index/edit/live-preview all render and persist correctly.

Deviations/notes from the build:

- **Admin controllers now live under `App\Http\Controllers\Admin\*`** (`SurveyTemplateController`, `SurveyTemplateQuestionController`, `SurveyThemeController`), unlike Phase 1's flat `App\Http\Controllers\*`. Deliberate: Phase 1 only had 3 controllers so flat was fine; Phase 2 adds enough admin-only controllers that namespacing them keeps the tree scannable as more phases land.
- **Bug fixed during manual verification**: `question-type-fields.blade.php` had a null-safe operator chaining mistake — `optional($x)?->contract()->builderGroup()` only guards the `contract()` call, not the chained `->builderGroup()`, so it threw "call to member function on null" whenever `$x` was null (i.e. every time the "Add Question" panel rendered, since there's no selected type yet). Fixed to `$x?->contract()?->builderGroup() ?? ''`. Caught by the manual browser-level walkthrough, not by the Pest suite — worth remembering that Pest's HTTP assertions don't always exercise every Blade branch a real page load does.
- Reordering, duplication, and options-parsing all confirmed via both Pest tests and live HTTP requests against the running app.
