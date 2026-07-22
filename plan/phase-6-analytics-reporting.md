# Phase 6 — Analytics & Reporting

## Context

Phases 1–5 built everything needed to configure a survey, collect responses, and reach customers through campaigns — but nobody has anywhere to actually *see* what came back. `responses`/`response_answers` have been accumulating since Phase 4 with nowhere to look at them. Phase 6 is Module 8 (Analytics) and Module 10 (Reports) in `task.md` — §16's dashboard and §19's export/scheduling requirements.

This is also the **first phase that adds real functionality to the client portal**, not just the admin side. `task.md` §5 explicitly lists "View dashboard," "View responses," and "Export reports" as things a Client *can* do — Phase 1 built the portal's dashboard as a placeholder ("your stats will appear here once your first survey goes live") specifically anticipating this. That placeholder gets replaced with real data now, scoped to that client only, with no Spatie permission needed on the `client` guard (consistent with Phase 1's decision that portal capability is currently uniform across owner/manager/staff).

---

## 1. Scope (from `task.md` §16, §19)

- **Analytics dashboard**: total responses, today's responses, completion rate, average completion time, NPS/CSAT/CES (whichever applies to a survey's primary question), average rating, per-question breakdown, positive/negative feedback lists, recent responses, charts, date-range filters.
- **A response detail view** — nothing currently lets anyone drill into a single response's individual answers; "Recent Responses" and "Negative Feedback" lists are useless without something to click into.
- **Exports**: PDF, Excel, CSV — both packages (`barryvdh/laravel-dompdf`, `maatwebsite/excel`) are already installed from Phase 5, so this phase needs zero new dependencies for exporting.
- **Scheduled email reports**: weekly/monthly/quarterly, per the `reports` table already named in the master schema.
- Available to **both** Survyra Admin (any client, any survey) and Client portal users (their own client only).

---

## 2. Database

Only one new table — everything else this phase needs already exists:
- `reports` — client_id, type (pdf/excel/csv), frequency (weekly/monthly/quarterly), recipients (json array of emails), survey_id (nullable = all of this client's surveys), last_sent_at, next_run_at, is_active, created_by.

---

## 3. Architecture decisions

- **One `AnalyticsService`, shared by both admin and portal.** It takes a client (and optionally a single survey) and a date range, and returns one shape of data — everything downstream (views, exports) consumes that same shape. There's no separate "admin analytics" vs "portal analytics" computation; the only difference between the two surfaces is which client they're allowed to pass in, enforced at the controller/policy layer, not duplicated in the math.
- **NPS/CSAT/CES only render when applicable, never guessed.** A client's surveys can each have a different primary-scoring question type. The dashboard shows whichever metrics are actually applicable to the survey(s) in view (an NPS breakdown only appears when at least one included survey's primary question is `nps`, same for CSAT/CES/average-rating) — it never manufactures an NPS-shaped number out of a CSAT survey's data.
- **NPS is computed the standard way**: % Promoters (score 9–10) − % Detractors (score 0–6), from `response_answers.score` on each response's primary question, not from `responses.score`/`sentiment` (those reflect the Thank-You-rule bucket a response landed in, which is a coarser, admin-configurable 3-way split — useful for "positive/neutral/negative feedback" lists, not for the precise NPS/CSAT/CES formulas).
- **Question-level breakdowns are tables and simple width-percentage bars, not one ApexChart per question.** A survey can have many questions; instantiating a full chart library instance for each is overkill for what's fundamentally "here's the distribution of answers." ApexCharts (already in the stack, loaded via CDN like every other frontend library since Phase 1) is reserved for the two visualizations that actually benefit from it: a response-volume trend line and a sentiment-breakdown donut.
- **Reports can be configured by both Admin and the Client portal.** `task.md` §5 lists "Export reports" as something a Client can do; nothing restricts *scheduling* one, and it's their own data — so unlike surveys (admin-only, per §5's explicit restriction), report configuration is open to both surfaces, gated only by guard/ownership, no extra permission needed on the portal side.
- **Every filter/pagination/report interaction after the initial page load is AJAX — no full-page reloads.** The dashboard's initial load is still server-rendered for a fast first paint (same reasoning as Phase 4's public survey page), but changing the date range, switching the survey selector, paging through responses, and creating/deleting a scheduled report all fire a `fetch()` and swap in a returned HTML fragment rather than navigating — this is what makes the admin/portal feel fast and premium instead of a stack of server-rendered form posts. This keeps the existing "Blade + vanilla AJAX" pattern rather than introducing a client-side templating layer: the endpoint renders the same Blade partial it would on a full load, just returns it as a fragment. The one exception is the two ApexCharts, which need their own JS-side series data instead of an HTML swap — the AJAX response carries a small `chartData` JSON block alongside the HTML fragment, and JS calls `chart.updateSeries()` rather than destroying/rebuilding the chart. Response detail opens in a modal fetched via AJAX rather than a separate page, for the same reason. File exports (PDF/Excel/CSV) stay plain links — clicking a download doesn't navigate the browser away from the page in the first place, so there's no reload to eliminate there.
- **On-demand export reuses the exact same query/format code the scheduled command uses.** One `ReportExportService` produces a PDF/Excel/CSV given a client + optional survey + date range; the "download it now" controller action and the "email it on a schedule" console command both call it — no separate, potentially-drifting export logic for the two paths.
- **Scheduled sending is a daily console command**, not per-report cron entries. `reports:send-scheduled` runs once daily, finds every active report whose `next_run_at` has passed, generates and emails it, then advances `next_run_at` by its frequency. Simple, and consistent with Phase 4's `responses:mark-abandoned` pattern.

---

## 4. Implementation breakdown

**Models**: `Report` (belongsTo Client, Survey nullable, User as createdBy).

**Services**:
- `AnalyticsService` — `forClient(Client, ?Survey, DateRange): AnalyticsSnapshot` (a DTO holding: total/today/completion-rate/avg-time, NPS/CSAT/CES/avg-rating when applicable, per-question breakdowns, sentiment counts, recent responses).
- `ReportExportService` — `toPdf`/`toExcel`/`toCsv`, all built from the same `AnalyticsService` snapshot plus the raw response/answer rows.
- `ReportService` — CRUD for scheduled `reports`, `dueReports()`, `advanceSchedule(Report)`.

**Console command**: `app/Console/Commands/SendScheduledReports.php`, scheduled daily.

**Controllers**:
- `App\Http\Controllers\Admin\AnalyticsController` — dashboard for any client/survey.
- `App\Http\Controllers\Admin\ResponseController` — index (filterable list) + show (single response detail) for a survey.
- `App\Http\Controllers\Admin\ReportController` — manage scheduled reports + on-demand export, for any client.
- `App\Http\Controllers\Portal\AnalyticsController` — same dashboard, scoped to `auth('client')->user()->client`.
- `App\Http\Controllers\Portal\ResponseController` — same, scoped.
- `App\Http\Controllers\Portal\ReportController` — same, scoped.

**Routes**: admin routes gated by the existing `view-analytics` permission (seeded in Phase 1, unused until now); portal routes just need `auth:client` (no new permission).

**Views**: shared partials under `resources/views/analytics/*` (stat cards, the two ApexCharts, the question-breakdown table/bars, recent-responses table, positive/negative-feedback lists) included by both `admin/analytics/*.blade.php` (wrapped in `x-admin-layout`) and `portal/analytics/*.blade.php` (wrapped in `x-portal-layout`) — same markup, different chrome, avoiding duplicating the significant chart/table code.

---

## 5. Tests (Pest)

- `AnalyticsService`: NPS/CSAT/CES computed correctly from known answer sets (including the promoter/passive/detractor NPS split); completion rate and average completion time match hand-calculated expectations; a CSAT-primary survey never produces an NPS figure.
- Question-level breakdown counts match seeded answers for both choice-type and scale-type questions.
- Admin can view any client's dashboard/responses/reports; a `client` guard user can only ever see their own client's data, never another client's, even by guessing a survey/response ID (403/404, not silently empty).
- Response detail view shows every answer for that response, correctly rendered per question type.
- Exporting to PDF/Excel/CSV produces valid, non-empty files containing the expected response data.
- `reports:send-scheduled` sends only reports whose `next_run_at` has passed, advances `next_run_at` by the right amount per frequency, and leaves not-yet-due reports untouched.
- Permission/guard isolation mirroring every prior phase.

---

## 6. Verification

- `php artisan test` — new Phase 6 suites green alongside the existing 85 tests from Phases 1–5.
- Manual walkthrough: as Survyra Admin, open the dashboard for Demo Cafe's published survey (already has responses from Phase 4/5's manual testing) → confirm NPS/completion-rate/recent-responses render → drill into one response's detail page → export a CSV and a PDF, confirm both open correctly → create a weekly scheduled report → run `reports:send-scheduled` manually and confirm an email lands (log driver) and `next_run_at` advances → log in as the Demo Cafe portal user → confirm the same dashboard now shows real data instead of the Phase 1 placeholder, scoped only to Demo Cafe.

---

## Phase 6 status: DONE

Built: `reports` migration + `Report` model, `AnalyticsService` (NPS/CSAT/CES/rating, completion rate, avg completion time, sentiment counts, question breakdowns, trend series), `ReportExportService` (PDF via DomPDF, Excel/CSV via maatwebsite/excel, all three sharing one `ResponsesExport`/`pdf.report` view), `ReportService` (CRUD + `dueReports()`/`advanceSchedule()`), `SendScheduledReports` console command scheduled daily, six controllers (`Admin\{Analytics,Response,Report}Controller`, `Portal\{Analytics,Response,Report}Controller`), shared `resources/views/analytics/*` partials reused by both surfaces, and the AJAX-everywhere admin/portal dashboard pages (tabs for Dashboard/Responses/Scheduled Reports, ApexCharts trend+sentiment charts updated via `updateSeries()` instead of rebuilt, AJAX modal for response detail, AJAX pagination, AJAX report create/delete) per the user's explicit "use ajax everywhere, minimize reloading" directive. 11 new Pest tests, 96 total passing. Verified end-to-end over real HTTP (not just `php artisan test`): seeded 7 real responses through the actual public survey flow, logged in as both the Survyra Admin and the Demo Cafe portal user, and exercised every endpoint (dashboard stats, NPS calculation, responses list, response-detail modal, report create/delete, CSV/PDF/Excel export) with curl before resetting the dev DB to a clean seeded state via `migrate:fresh --seed`.

Real bugs found and fixed during this phase:

1. **`Client::surveys()` relation didn't exist.** `AnalyticsService` (and the admin/portal controllers) call `$client->surveys()`, but no prior phase had ever added that relation to the `Client` model — `Survey` only had the inverse `belongsTo`. Every dashboard load would have fatally errored on the very first query. Added the missing `HasMany`.
2. **`reports.next_run_at` migration failed under MySQL strict mode.** Declared as a non-nullable `timestamp` with no default, which MySQL rejects outright ("Invalid default value for 'next_run_at'") since it refuses to invent a default for a NOT NULL timestamp column in strict mode. The app always supplies `next_run_at` at creation time via `ReportService::create()`, so there's no real need for a DB-level default — made the column nullable instead.
3. **`@json()` silently corrupts nested-array literals.** Blade's `@json($expr, $options, $depth)` directive compiles by doing a naive `explode(',', $expression)` to separate its three positional arguments — it has no idea that commas can be nested inside `[]`. Passing the chart-seed array as an inline literal (`@json(['trend' => [...], 'sentiment' => [...]])`) got chopped at the first few top-level-looking commas, producing an unclosed-bracket PHP parse error in the compiled view. Fixed by building the array in a `@php` block first and calling `@json($chartSeed)` with a single variable reference — the safe pattern for anything beyond a trivial literal.
4. **Carbon 3 changed `diffInSeconds()`'s default sign behavior.** `$absolute` now defaults to `false` (it defaulted to `true` in Carbon 2), so `completed_at->diffInSeconds(started_at)` returned a negative number and every dashboard would have shown a nonsensical negative average completion time. Fixed by passing `true` explicitly wherever a magnitude (not a signed direction) is wanted.
5. **`ScheduledReportMail implements ShouldQueue`, which changes how it must be tested.** Queuing is the right call here — it keeps the daily `reports:send-scheduled` command from blocking on SMTP once per recipient — but `Mail::fake()` records `ShouldQueue` mailables under a separate "queued" bucket rather than "sent", so `Mail::assertSent()` reports zero even though the mail genuinely dispatched. Tests use `Mail::assertQueued()` instead; noting this so a future phase doesn't "fix" a false negative by removing the queue.

No deviations from the plan's scope or architecture — the AJAX/fragment/chart-seed approach, the shared `AnalyticsService` computation, and the six-controller split all matched section 3's design as written.
