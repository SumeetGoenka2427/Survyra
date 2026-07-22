
# Survyra Platform — Development Plan

## Context

`task.md` is a product/architecture blueprint for Survyra, a Laravel-based managed CX/feedback/reputation platform. The project directory is currently empty except for that blueprint — this is a greenfield build. Before writing any code, dev rule #2 in the blueprint itself requires "complete database design before coding," and the user asked for a plan plus an explicit call-out of what the blueprint leaves unspecified. This plan does three things: (1) surfaces the gaps/ambiguities in the blueprint with a recommended resolution for each, (2) lays out the full database schema across all modules so later phases don't require re-architecture, and (3) gives an implementation-ready breakdown of Phase 1 (Foundation), with Phases 2–8 kept at module/table/deliverable level until we reach them.

Decisions already confirmed with the user:
- Plan covers full DB schema + all-phase module breakdown now; only Phase 1 gets file/class-level detail.
- Clients are multi-user tenants (`client_users` table), not one login per company.
- Roles/permissions use `spatie/laravel-permission`.

---

## 1. Gaps in the blueprint (and resolutions)

The blueprint describes *what* the product does well but leaves several *how* decisions unspecified. Flagging each so they're conscious choices, not accidents:

1. **Multi-tenancy isolation** — not stated. → Single MySQL database, every tenant table carries `client_id`, enforced via a global Eloquent scope + policy checks. Matches the blueprint's own "never create a table per survey" rule.
2. **Client-side multi-user access** — resolved: `client_users` table, separate auth guard, roles scoped within a client (owner/manager/staff).
3. **Subscription/plan enforcement** — `clients.subscription` is listed as a field, but no plan model or quota logic exists anywhere else, and billing itself is Phase 8. → Add a `subscription_plans` table now (manually assigned by Survyra Admin, no payment gateway yet) so quotas (max active surveys, monthly responses, campaign sends) exist as data from day one, even if only soft-enforced (warnings) until Phase 8 wires real billing.
4. **Survey versioning on edit-after-publish** — undefined. Editing a published survey that already has responses is dangerous (question IDs shift meaning). → Add a `version` int to `surveys`; once a survey has ≥1 response, structural edits (remove/reorder/retype questions) force a new version rather than mutating in place. Non-structural edits (theme, thank-you rules) can mutate freely. **This needs product sign-off**, not just engineering — flagging explicitly.
5. **Conditional logic engine storage** — spec lists operators but not a schema. → `survey_logic_rules` stores a nested JSON condition tree (AND/OR groups of `{question_id, operator, value}`) per rule, plus an action (`show`/`hide`/`skip_to`). A `LogicEngine` service evaluates it against in-progress answers.
6. **"Add new question types without changing existing code"** — spec demands this but doesn't say how. → A `question_types` registry table (key, label, scoring behavior, settings schema) paired with a code-side Strategy interface (`QuestionTypeContract`: `validate()`, `render()`, `score()`). New type = one DB row + one class implementing the interface, registered in a config map — no edits to existing types' code.
7. **Thank-you engine scoring source** — examples show NPS-based positive/neutral/negative, but not every survey will have an NPS question. → Each survey designates one "primary scoring question" (or a computed composite) at build time; thank-you rules evaluate against that score, with optional secondary per-question conditions.
8. **Public survey link structure** — subdomain vs custom domain vs path not specified. → MVP uses path-based links (`survyra.com/s/{slug-or-uuid}`); custom domains are deferred to the Phase 8 white-label item, which the blueprint already lists as future.
9. **SMS/WhatsApp regulatory prerequisites** — the blueprint recommends Indian providers but never mentions that Indian SMS requires **TRAI DLT template registration** and WhatsApp requires **Meta-approved message templates**. Neither is a code task — both are account/compliance setup that blocks Phase 5 going live regardless of how well the campaign module is built. Flagging as an external dependency to start early.
10. **Contact consent enforcement** — `contacts.consent` is a field, but nothing says it's enforced. → Campaign recipient selection excludes non-consented contacts by default; consent source/timestamp is logged.
11. **Notifications module (Module 12) scope** — undefined. → MVP scope: in-app notifications (Laravel's built-in notifications table) for both admin and client portal, plus email alerts for two trigger events: negative feedback received, campaign send completed/failed.
12. **Settings module (Module 11) scope** — undefined. → Generic key-value `settings` table scoped either globally (`client_id` null, Super Admin only) or per-client (theme defaults, alert thresholds), read through a small `SettingsService` with cache.
13. **File storage location** — Intervention Image is in the stack, but not where files live. → Local `storage/app/public` disk for MVP, but all file access goes through Laravel's `Storage` facade (not hardcoded paths) so swapping to S3 later (matches the blueprint's own "CDN Ready" goal) is a config change, not a rewrite.
14. **Which fields get encrypted** — "Encrypted Sensitive Data" is listed under Security with no field list. → Encrypt `contacts.phone`, `contacts.email`, and `clients.whatsapp_number`/`support_number` via Eloquent's `encrypted` cast. Flag: this breaks `WHERE phone = ?` lookups — if exact-match contact search is needed later, that requires a blind-index column, deferring that unless the user confirms it's needed.
15. **Timezone handling** — clients have a `timezone` field, but no storage convention stated. → Store all timestamps in UTC in DB; convert to `client.timezone` only at display time via a Blade/view helper.
16. **Test framework** — dev rule #10 mandates tests but not which framework. → Recommend **Pest** (modern Laravel default, plain PHPUnit under the hood, less boilerplate).
17. **Deployment target** — not stated, but it affects whether queue workers/Redis are realistically available from day one. → Build queue-abstracted (`QUEUE_CONNECTION=database` initially, code has no direct Redis coupling) so it degrades gracefully on shared hosting and upgrades cleanly on a VPS.

---

## 2. Full database schema (all phases, designed up front)

Grouped by the module they primarily serve; `client_id` FKs imply tenant scoping unless noted.

**Identity & tenancy**
- `users` — internal staff (Super Admin, Survyra Admin). Breeze default columns + spatie roles.
- `clients` — company_name, industry, logo_path, email, phone, website, address, google_review_url, facebook_url, instagram_url, linkedin_url, youtube_url, support_number, whatsapp_number, brand_color, secondary_color, timezone, language, status, subscription_plan_id (FK), created_by (FK users), soft deletes.
- `client_users` — client_id (FK), name, email, password, role (owner/manager/staff), is_active, soft deletes.
- `subscription_plans` — name, slug, max_active_surveys, max_monthly_responses, max_monthly_campaign_sends, price, billing_cycle, is_active.

**Templates & question types**
- `question_types` — key, label, scoring_type, settings_schema (json), is_active. Seeded with the 15 spec'd types.
- `survey_templates` — name, industry_category, description, is_active, created_by.
- `survey_template_questions` — survey_template_id, question_type_id, question_text, options (json), settings (json), order, is_required.

**Surveys**
- `surveys` — client_id, survey_template_id (nullable), title, slug, version, theme_id, status (draft/published/archived), settings (json), published_at, created_by, soft deletes.
- `survey_questions` — survey_id, question_type_id, question_text, options (json), settings (json), order, is_required.
- `survey_logic_rules` — survey_id, source_question_id, conditions (json rule tree), action, target_question_id/target_section_id, priority.
- `survey_thankyou_rules` — survey_id, condition (score range or question/operator/value), sentiment, thank_you_message, show_google_review/facebook/instagram/website/coupon (bools), coupon_code, show_complaint_form/support_number/whatsapp_button (bools), manager_contact (json), priority.
- `survey_themes` — name, is_system, client_id (nullable override), logo_path, primary_color, secondary_color, background, button_style, font, progress_bar_style, border_radius, custom_css.

**Responses**
- `responses` — uuid, client_id, survey_id, contact_id (nullable), status, device, browser, ip, location (json), started_at, completed_at, score, sentiment, source (sms/whatsapp/email/qr/direct), campaign_id (nullable).
- `response_answers` — response_id, question_id, answer, score, timestamps (upserted per-question via AJAX autosave).

**Contacts & campaigns**
- `contacts` — client_id, name, phone (encrypted), email (encrypted), city, consent, consent_source, created_at.
- `contact_tags` / `contact_tag_pivot` — client-scoped tags for campaign targeting.
- `campaigns` — client_id, survey_id, name, type (sms/whatsapp/email/qr/short_link), status, scheduled_at, sent_at, message_template, provider, stats (json cache), created_by.
- `campaign_recipients` — campaign_id, contact_id, channel, status, sent_at/delivered_at/clicked_at/responded_at, error_message, provider_message_id, short_url.
- `short_links` — code, target_url, campaign_recipient_id (nullable), click_count, last_clicked_at.
- `qr_codes` — client_id, survey_id, label, format, file_path, short_link_id.

**Reporting, settings, ops**
- `reports` — client_id, type (pdf/excel/csv), frequency, recipients (json), survey_id (nullable=all), last_sent_at, next_run_at, is_active, created_by.
- `settings` — client_id (nullable=global), key, value (json), group.
- `activity_log` — via `spatie/laravel-activitylog` package migration.
- Laravel's built-in `notifications` table for Module 12.

---

## 3. Architecture decisions

- **Packages beyond the blueprint's list**: `spatie/laravel-permission` (roles/permissions), `spatie/laravel-activitylog` (audit log requirement in §20), `pestphp/pest` (testing).
- **Two auth guards**: `web` for internal `users` (Super Admin / Survyra Admin) at `/admin/*`, and a `client` guard for `client_users` at `/portal/*`. Separate login pages, separate middleware, no shared session — a Client user must never be able to reach `/admin/*` and vice versa.
- **Service + Repository layering** (per dev rule #4): `app/Services/*Service.php` holds business logic, `app/Repositories/*Repository.php` + `app/Repositories/Contracts/*Interface.php` handle persistence, bound in a `RepositoryServiceProvider`. Controllers stay thin; Form Requests handle validation (dev rule #6).
- **Question-type extensibility**: `QuestionTypeContract` interface + a config-driven registry (`config/question_types.php` mapping `key => FQCN`), so Module 4/5/10 code never branches on type strings directly.
- **Public survey pages stay framework-light**: plain Blade + vanilla AJAX (no Livewire) per the blueprint's own performance mandate in §4/§14, since this is the highest-traffic, lowest-context surface (SMS/WhatsApp/QR clicks on mobile). Livewire is reserved for internal admin/portal UI (dashboards, survey builder) where the reduced payload doesn't matter as much.

---

## 4. Phase-by-phase roadmap (module/table/deliverable level)

| Phase | Modules covered | Key new tables | Deliverable |
|---|---|---|---|
| **1 – Foundation** | Auth, Roles, Client Mgmt, Dashboard | users, clients, client_users, subscription_plans, settings, activity_log | Two working logins (admin + portal), client CRUD, role-gated dashboards |
| **2 – Template Library** | Survey Templates, Question Types, Themes | question_types, survey_templates, survey_template_questions, survey_themes | 10 seeded templates across 5 industries, 7 seeded themes, all editable |
| **3 – Survey Builder** | Survey Builder, Logic, Thank You Engine, Publish | surveys, survey_questions, survey_logic_rules, survey_thankyou_rules | Admin can clone a template into a client survey, reorder/edit/logic-gate questions, configure thank-you rules, publish |
| **4 – Response Engine** | Survey Responses | responses, response_answers | Public survey page with per-question AJAX autosave, QR/link generation, resumable sessions, LogicEngine wired live |
| **5 – Campaign Manager** | Campaign Mgmt, Contacts, QR | contacts, contact_tags, campaigns, campaign_recipients, short_links, qr_codes | Send via SMS/WhatsApp/Email with delivery/click tracking (contingent on DLT/Meta template approval, see gap #9) |
| **6 – Analytics & Reporting** | Analytics, Reports | reports | NPS/CSAT/CES dashboards, PDF/Excel/CSV export, scheduled email reports |
| **7 – Reputation Management** | Notifications, Review Analytics | notifications | Negative-feedback alerting, review-click analytics on top of Phase 3's thank-you rules |
| **8 – Optimization & Future** | AI, API, White-label, Billing | (future) | Out of scope for MVP; billing tables/webhooks designed only when reached |

---

## 5. Phase 1 (Foundation) — implementation-ready detail

**Packages to install**: `laravel/breeze`, `spatie/laravel-permission`, `spatie/laravel-activitylog`, `intervention/image` (needed immediately for client logo upload).

**Migrations**: `users` (Breeze default, extended with `is_active`), `clients`, `client_users`, `subscription_plans`, `settings`, plus package migrations for permissions and activity log.

**Models**: `User`, `Client`, `ClientUser`, `SubscriptionPlan`, `Setting` — `Client`/tenant-scoped models get a `BelongsToClient`-style global scope trait once `client_users` exist to scope against.

**Auth**: dual-guard setup — `config/auth.php` gets a `client` guard + `client_users` provider; `routes/admin.php` (Super Admin + Survyra Admin, Breeze-scaffolded) and `routes/portal.php` (Client users, separate login view) both included from `routes/web.php`. Middleware aliases `role:super_admin`, `role:survyra_admin`, `guard:client` enforce separation.

**Authorization**: Spatie roles seeded (`super_admin`, `survyra_admin`) for the `users` table guard, and (`owner`, `manager`, `staff`) for the `client` guard; a `ClientPolicy` gates client CRUD to internal roles only, per §5 (clients "cannot build surveys, delete surveys, edit logic").

**Service/Repository skeleton**: `app/Repositories/Contracts/ClientRepositoryInterface.php` + `ClientRepository`, `app/Services/ClientService.php`, bound in `RepositoryServiceProvider`. Establishes the pattern every later phase follows.

**Client Management CRUD**: `ClientController` (index/create/store/edit/update/toggle-status/destroy-soft), `StoreClientRequest`/`UpdateClientRequest` Form Requests, logo upload through Intervention Image to the `public` disk.

**Dashboard**: role-aware dashboard views — Super Admin sees system-wide stats + client list; Survyra Admin sees assigned clients; Client portal sees a placeholder (no survey data exists until Phase 4, so this is stat-tile scaffolding wired to real queries later).

**Blade structure**: `resources/views/components/{admin-layout,portal-layout,forms/*,stat-card,alert}.blade.php`, Bootstrap 5, mobile-first per §4.

**Tests (Pest)**: auth flow for both guards, guard cross-access denial (client_user hitting `/admin/*` → 403/redirect), client CRUD, role-gated policy checks.

---

## 6. Verification

- `composer install`, `php artisan migrate --seed` (seeders for the two internal roles + a demo client/client_user).
- `php artisan test` (Pest) — auth, guard-isolation, and client CRUD suites green.
- `php artisan serve`, then manually walk both logins in-browser: Super Admin dashboard → create a client → log out → log in as that client's `client_user` → confirm the portal dashboard loads and `/admin/*` is inaccessible. This satisfies the "test the golden path in a browser" rule before calling Phase 1 done.

---

## 7. Phase 1 status: DONE (as of this build)

Everything in section 5 above has been implemented and verified end-to-end (migrations run against MySQL, 28 Pest tests passing, manual login/CRUD/guard-isolation walkthrough via curl). Deviations made during the build, not anticipated in the original plan:

- **CDN instead of Vite/npm build**: the dev machine's Node.js (v16.20.2) is incompatible with the Vite 7 / Tailwind 4 that `breeze:install` pulls in. Rather than upgrade Node system-wide, the npm/Vite pipeline was dropped entirely — Bootstrap 5 and Bootstrap Icons are loaded via CDN `<link>`/`<script>` tags instead. Fully functional; revisit once Node is upgraded if local asset bundling becomes worthwhile.
- **Trimmed Breeze auth surface**: self-registration, email verification, password-confirmation, and account self-deletion were all removed. Nothing in the blueprint calls for public signup — Survyra Admin creates clients, and each client's first portal login is created in the same step — so these Breeze defaults were dead weight.
- **`client_users.role` is a plain column, not Spatie-managed**: owner/manager/staff is currently just a label, since Phase 1 doesn't yet differentiate their permissions per the blueprint. Spatie protects the real permission boundary (internal staff: Super Admin vs Survyra Admin).

Demo credentials (seeded, password `password` for all): Super Admin `sumeet@podup.com` at `/admin/login`, Survyra Admin `admin@survyra.com` at `/admin/login`, demo client owner `owner@democafe.test` at `/portal/login`.
