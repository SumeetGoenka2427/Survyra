# Survey Design Modernization — Implementation Plan

## Context

This is now the top priority, ahead of the remaining `plan/redesign-plan.md` stages (Clients/Surveys-list/Campaigns admin UI) — those stay pending and resume after this. The ask: redesign the actual survey-taking experience (what a respondent sees at `/s/{slug}`) to feel like Typeform/Qualtrics/SurveyMonkey/Tally/Microsoft Forms, with multiple selectable presentation layouts per survey and multiple display styles per question type, all theme-driven, **with zero changes to scoring, validation, logic-engine, or thank-you-rule behavior**.

## Architecture decisions

- **Styles are a rendering concern only.** A question's `score()`/`validationRules()` never change based on style — only which Blade partial renders the input. This is enforced structurally: `renderComponent(string $style)` picks a view, but the view still emits the exact same `name="answer"`/`name="answer[]"` fields with the exact same value domain (e.g. NPS is always an integer 0-10 regardless of whether it's drawn as number boxes, an emoji-grouped scale, circles, or a gradient row) — so `ResponseService::saveAnswer()`, `QuestionTypeContract::score()`, and every existing Phase 4 test are untouched and still valid.
- **Style is stored in the existing `settings` JSON column** (`survey_questions.settings['display_style']` / `survey_template_questions.settings['display_style']`) — no new column, reuses the column every question already has. `SurveyService::createFromTemplate()` already copies `settings` wholesale from template question to survey question, so a template's chosen styles carry over to every survey created from it automatically, with no extra code.
- **`QuestionTypeContract` gains two additions**: `availableStyles(): array` (style key → label, `['default' => 'Default']` if a type only has one look) and `renderComponent()` gains an optional `string $style = 'default'` parameter. `AbstractQuestionType` provides both as sane defaults, so most question types need zero changes; only the types getting real multi-style treatment override them.
- **Layout is a survey/template-level choice**, stored in a new nullable `layout` column on both `survey_templates` and `surveys` (copied over at `createFromTemplate()` time, just like `theme_id`). This is a structural/flow concern, not a per-question one.
- **Theme variables become real CSS custom properties.** Today `survey/show.blade.php` hardcodes theme colors into a handful of specific classes (`.btn-survyra-primary`, `.survey-card`, …). This pass converts that into `--survey-primary`/`--survey-secondary`/`--survey-radius`/`--survey-button-radius`/`--survey-font` custom properties set once per page load, and a new shared stylesheet (`survey-experience.css`) references those variables everywhere — so every new style variant (cards, pills, circles, gradient, hearts, toggle, floating labels) automatically reskins itself to whatever theme is active, satisfying "configurable through themes" structurally rather than by duplicating color logic per style.
- **The existing multi-step response engine (Phase 4) is preserved as-is and becomes the `multi_step` layout.** Its AJAX-per-question flow, resumability, cookie handling, and LogicEngine integration are the most fragile/valuable part of the app — this plan does not touch `ResponseService`, `LogicEngine`, or the `/answer` and `/submit` endpoints' contracts at all. New layouts are additional *views* over the same controller/response data, not a new engine.

## What ships in this pass vs. what's flagged as follow-up

**Layouts** — 5 were requested (one-page, multi-step, conversational, card-based, section/wizard). Shipping:
- `multi_step` (existing engine, kept, now themeable via the new CSS variables) — default.
- `conversational` — a real second layout: full-viewport, large centered typography, no boxed card, smooth fade/slide transition between questions, minimal chrome. This is a template/CSS/markup change only, riding the exact same AJAX question-by-question flow as `multi_step` — safe to build in one pass.
- **Not shipped this pass, flagged honestly**: `one_page` (rendering every question at once and re-evaluating LogicEngine show/hide client-side as answers change is a real flow redesign, not a skin — needs its own design pass), `card_based` and `section_wizard` (each is closer to a variant of one-page/multi-step than a wholly new engine, but depend on one-page landing first). The layout picker in the builder will only offer `multi_step`/`conversational` for now rather than show non-functional options.

**Question styles** — shipping real multi-style support (2-4 looks each, selectable per question) for the 8 types where the request gave concrete examples:
- NPS: number boxes (default), emoji-grouped scale, circular buttons, gradient row
- Rating Stars: stars (default), hearts, numbered badges
- Emoji Rating: emoji row (default), labeled cards
- Radio (multiple choice): buttons (default), modern cards, pill chips
- Checkbox: boxes (default), modern cards, pill chips
- Yes/No: buttons (default), segmented toggle
- Textbox: modern input (default), floating label
- Textarea: modern auto-resizing (default), floating label

The remaining 7 types (CSAT, CES, Dropdown, Number, Email, Phone, Date) get the same premium CSS treatment (theme-driven colors/radius/typography, better spacing, focus states) as a single modernized "default" look, but not multiple selectable styles yet — noted here rather than silently skipped.

**Net-new question types explicitly out of scope here**: the request mentions Matrix, Ranking, and Slider question types getting redesigned — **none of these exist in the app today** (`config/question_types.php` has 15 types; Matrix/Ranking/Slider aren't among them). Building a brand-new question type is a business-logic addition (a new `QuestionTypeContract` implementation with real `score()`/`validationRules()` behavior, a DB `question_types` row, builder settings-panel support) — not a redesign of something existing, and explicitly against "without changing existing business logic" if bundled in silently. Flagging as a separate future ask rather than inventing scoring semantics for three new types unasked.

## Builder UI changes

- `question-type-fields` shared component (used by both the survey builder and template builder) gets a "Display Style" dropdown, populated per selected question type from `availableStyles()`, saved into `settings.display_style` via the existing `StoreSurveyQuestionRequest`/`StoreTemplateQuestionRequest` `prepareForValidation()` merge (one more key in the same `array_filter` block each already builds).
- Template create/edit and survey builder get a "Layout" selector (`multi_step` / `conversational`), persisted on `survey_templates.layout` / `surveys.layout`.

## Verification

- All existing Phase 4 tests (survey response flow, logic rules, thank-you rules, resume-cookie behavior) stay green untouched — proves scoring/validation truly didn't move.
- New Pest coverage: selecting a style persists and round-trips through `createFromTemplate()`; `renderComponent()` falls back to a valid style key if an invalid one is ever stored; answering a styled question (e.g. NPS rendered as "gradient") scores identically to the default style.
- Manual curl walkthrough of at least: NPS in all 4 styles, Radio in all 3 styles, both layouts, on the public survey page - confirming markup renders, theme colors thread through, and answers still submit/score correctly.

## Final status: everything in this doc's scope is now DONE

Every item this doc originally deferred has since shipped in later passes:

- **All 5 requested layouts** are built: `multi_step`, `conversational` (from this pass), plus `one_page`, `card_based`, and `section_wizard` (built in later sessions — see `plan/pending-work-master-plan.md` Track B for their individual status write-ups).
- **CSAT/CES/Dropdown/Number/Email/Phone/Date multi-style expansion** — done. See the master plan's dedicated status section for the exact styles each type got.
- **Matrix/Ranking/Slider** — the "separate future ask" flagged above — are now built as real `QuestionTypeContract` implementations with genuine new scoring/validation logic, not a restyle. See the master plan's dedicated status section for implementation detail.
- **`admin.survey-preview`** now reflects all 5 layouts' actual pacing instead of always showing the single-question stepper mock.

The one remaining known limitation from the one-page/card-based/section-wizard work (answers not visually pre-filled into fields on reload) is still deferred, tracked in `plan/pending-work-master-plan.md`, not in this doc's original scope.
