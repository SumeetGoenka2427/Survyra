# Survey Analytics Dashboard Redesign — Implementation Plan

## Context

Original ask (verbatim scope): completely redesign the **Survey Analytics Dashboard** (`admin.analytics.index`) into a premium, enterprise-grade, data-driven analytics surface — KPI cards, interactive charts, trend analysis, progress indicators, completion stats, performance comparisons, filters/drill-down, visual insights/recommendations — plus the **Client Analytics Dashboard** (clicking a client should open analytics, not Edit), general SaaS-grade UI/UX, and a broad set of visualization types (line/bar/pie/donut/area/heatmap/progress-ring/timeline/activity-feed/KPI-card/geo-map/comparative/trend/performance).

**This doc supersedes the stale claim in `plan/pending-work-master-plan.md` Track A Stage 7** ("Analytics/Reports — visual polish ✅ DONE"). That entry only covered inheriting Stage 0's global CSS tokens and re-theming ApexCharts colors — it did **not** redesign the page's layout, add missing chart types, or surface unused computed data. Verified directly against the current file (`resources/views/analytics/dashboard.blade.php`, 298 lines) before writing this plan: it is still `x-stat-card` grids + raw Bootstrap `.progress` bars for devices/browsers/sources/countries/drop-off + plain `list-group`s for feedback + a plain table for recent responses. Only 2 real ApexCharts exist (trend, sentiment). None of heatmap/progress-ring/timeline/insights-panel/survey-comparison-table exist anywhere in this view.

The **Client Analytics Dashboard** (`admin.clients.analytics`) is a separate, already-modern build (KPI cards, 10 ApexCharts, profile summary, recent activity) — see `plan/survey-analytics-dashboard-redesign-completion.md` for its status. This doc is scoped to the Survey Dashboard only.

## Data already computed but unused in the current view

`AnalyticsService::compute()` (`app/Services/AnalyticsService.php:68-178`) already returns several fields the current Blade view never renders — the redesign should surface these before adding any new queries:

| Field | Currently used? |
|---|---|
| `growth_rate` (vs. previous equal-length period) | ❌ not rendered anywhere |
| `weekly_trend` | ❌ not rendered anywhere |
| `hourly_distribution` | ❌ not rendered anywhere |
| `day_of_week_distribution` | ❌ not rendered anywhere |
| `active_surveys` / `total_surveys` | ❌ not rendered anywhere |
| `avg_daily_responses` | ❌ not rendered anywhere |
| `survey_count` | ❌ not rendered anywhere |

The Client Analytics Dashboard already has proven, working ApexCharts markup for `weekly_trend`/`hourly_distribution`/`day_of_week_distribution`-shaped data (`resources/views/admin/clients/analytics/index.blade.php`) — that markup/config can be copied near-verbatim rather than designed from scratch.

## Known pre-existing bug to fix first (Stage 0)

`AnalyticsService::weeklyTrend()` (`app/Services/AnalyticsService.php:574-593`) uses `selectRaw('YEARWEEK(started_at, 1) as week, ...')` — a MySQL-only function. Under the test suite's SQLite (`:memory:`) connection (`phpunit.xml:26-27`) this throws `QueryException: no such function: YEARWEEK`, which is why **7 of 11 `AnalyticsTest` tests currently fail** on a clean checkout (confirmed by running `php artisan test --filter=AnalyticsTest` — failure is pre-existing, not caused by anything in this session). Production runs MySQL so this doesn't crash live traffic today, but:
- it blocks ever having a green baseline to build the redesign against, and
- this plan wants to surface `weekly_trend` in the new dashboard, so the underlying query needs to be portable regardless.

Fix: replace the raw `YEARWEEK` grouping with the same PHP-side bucketing pattern `trend()`/`hourlyDistribution()` already use elsewhere in this file (group by `Carbon::parse($row->started_at)->startOfWeek()` in PHP after a plain date-ranged fetch, or grouped via `DB::raw` with a driver check) — no behavior change, just portability.

## New derived metrics/widgets to add (all from existing schema — no migrations)

- **Survey performance comparison table** — generalizes the `survey_performance` computation already built for the Client Dashboard (`AnalyticsService.php:372`) to produce one row per survey (completion rate, NPS/CSAT/CES if scored, avg completion time, response volume) when no single survey is selected in the filter. This is what turns "performance comparisons" from the spec into something real and sortable, not just a phrase.
- **Progress rings** — `completion_rate` / `abandonment_rate` as ApexCharts `radialBar`, satisfying the spec's explicit "Progress Rings" ask (not used anywhere in the app yet).
- **Hourly × day-of-week heatmap** — combine `hourly_distribution` and `day_of_week_distribution` into one real ApexCharts `heatmap` grid (response density by hour/weekday). Satisfies the spec's explicit "Heatmaps" ask — no heatmap exists anywhere in the codebase today.
- **Insights & recommendations panel** — a small rule-based generator (plain PHP thresholds over already-computed arrays, not an AI call, not new infra): e.g. "Completion rate {up/down} X% vs previous period" (from `growth_rate`), "Mobile completion is X pts lower than desktop" (cross `devices` vs `completion_rate`), "N surveys are under 50% completion — review question count/logic" (from the new per-survey comparison table). Satisfies "visual insights and recommendations where applicable" without inventing scoring semantics.
- **Recent Activity timeline** — replace the current two side-by-side positive/negative `list-group`s with one real vertical timeline (icon + timestamp + sentiment badge + "View" action), reusing `recent_responses`/`positive_responses`/`negative_responses` already fetched.
- **Geographic** — stays a ranked country bar/table (already computed via `countries`). No `lat`/`lng` exists anywhere in the schema (only a `country`/`city` string on `responses`), so a true choropleth map is a stretch item requiring a new mapping library under the "no build step, CDN only" constraint — flagged as optional/deferred, consistent with the identical call made in `plan/redesign-plan.md` for this same limitation.

## Stage-by-stage build plan

| Stage | Scope |
|---|---|
| 0 | Fix `YEARWEEK` portability bug in `weeklyTrend()`; confirm `php artisan test --filter=AnalyticsTest` is fully green before touching any view. |
| 1 | KPI summary card row — reuse the `ds-kpi-card` pattern already proven on the Client Dashboard: Total Responses, Completion Rate, Active/Total Surveys, Avg Completion Time, Response Growth (trend arrow from `growth_rate`), primary score metric (NPS/CSAT/CES) if the client has one. |
| 2 | Primary charts row — Response Trend (area, daily, already exists — keep), **Weekly Trend** (new bar chart, currently-unused data), Sentiment (donut, already exists — keep). |
| 3 | New **hourly × day-of-week heatmap**; restyle Devices/Sources/Countries from raw progress-bar rows to donut/pie/bar ApexCharts (directly reuse the exact working config already in `admin/clients/analytics/index.blade.php`). |
| 4 | New **survey performance comparison table** — sortable/searchable, satisfies the "beautiful tables with sorting, filtering, search" requirement. |
| 5 | **Progress rings** (completion/abandonment via `radialBar`); restyle Drop-off Funnel and Question Breakdown off raw `.progress` bars onto the same chart language. |
| 6 | **Recent Activity timeline** replacing the positive/negative list-groups; restyle Review & Action Clicks to match the Client Dashboard's existing bar-chart version. |
| 7 | **Insights & Recommendations panel** (rule-based, see above). |
| 8 | Filters/drill-down polish — client/survey/date filters already exist (`resources/views/analytics/filters.blade.php`); add sort/search to the new survey-comparison table and confirm `responses-table.blade.php` (Responses tab) already has adequate sort/search, extending it if not. |
| 9 | Dark-mode + responsive QA pass across every new component — reuses `design-system.css` tokens, already dark-mode-aware; no new CSS system needed. |

## Constraints carried over from prior plans (still true, don't relitigate)

- No build step — CDN Bootstrap 5 + ApexCharts + vanilla JS/Alpine, same stack as the rest of the app.
- No new DB columns/migrations — every widget above is derivable from `responses`/`survey_questions`/`clients`/`review_clicks` as they exist today.
- No changes to `ResponseService`/`LogicEngine`/scoring semantics — this is analytics read-side only.
- No fabricated data — if a metric isn't backed by real schema (e.g. true geo-coordinates), it stays a placeholder/flag, not a fake widget.

## Verification per stage

Same convention as every prior stage in this project: `php artisan test --filter=AnalyticsTest` stays green, a direct tinker-render check per modified view (the same technique used to verify the Client Dashboard bug fixes — render the Blade view against real seeded data with an authenticated user, not just visual review), then a real browser walkthrough once a few stages are stitched together.

## Final status: all 10 stages are now DONE (built 2026-07-22)

Full breakdown in `plan/survey-analytics-dashboard-redesign-completion.md`. Highlights and deviations from this doc's original plan, noted honestly:

- **Stage 0 scope grew**: not just `weeklyTrend()`'s `YEARWEEK()` — `hourlyDistribution()` (`HOUR()`) and `dayOfWeekDistribution()` (`DAYOFWEEK()`) were equally MySQL-only and would have thrown the moment any test exercised them. All three now share one `responseTimestamps()` + Carbon-bucketing pattern. `AnalyticsTest` went from 7 failed/4 passed to 11/11 passing.
- **The AJAX-fragment chart problem**: `analytics/dashboard.blade.php` is swapped via `innerHTML` on every filter change and every 30-second live-poll tick, and `innerHTML` assignment never executes embedded `<script>` tags. The 3 "always visible" charts (trend/weekly/sentiment) already lived outside the fragment in the persistent shell and updated via `updateSeries()` — that pattern was kept and extended (added weekly trend to it). Every *new* chart this plan calls for (heatmap, progress rings, devices/browsers/sources/countries, drop-off, question-breakdown, review-clicks) lives inside the fragment, so `analytics.js` gained a `initFragmentCharts()` function that destroys and recreates them from a JSON payload embedded in the fragment, called both on first paint and after every refresh — this is a new, documented, reusable pattern, not just a one-off hack.
- **Heatmap needed new data, not a recombination**: `hourly_distribution`/`day_of_week_distribution` are marginals; a real heatmap needs the joint (hour, day-of-week) cross-tab, which didn't exist. Added `AnalyticsService::hourByDayHeatmap()`.
- **Dark mode for charts went further than "don't break it"**: ApexCharts never followed the `data-bs-theme` toggle on *either* dashboard before this session — charts kept light-mode colors until a hard refresh. Added a `themeColors()` helper plus a `MutationObserver` on `data-bs-theme` that rebuilds every chart live when the toggle is clicked. This is an improvement over the pre-existing Client Dashboard too, not just parity for the new Survey Dashboard work.
- **Geographic map**: stayed a ranked bar chart as flagged — no `lat`/`lng` anywhere in the schema, consistent with the identical call already made in `plan/redesign-plan.md`.
- **Not done, flagged rather than skipped silently**: a manual mobile/accessibility audit on real breakpoints/screen readers wasn't performed — the grid is responsive by construction (Bootstrap) but wasn't hand-walked on real devices.

Verified throughout: `php artisan test` held at 154 passing / 1 pre-existing unrelated failure across every stage; every modified view was rendered end-to-end through its real controller against real seeded DB data (client "Spice Route Bistro", 64 responses; an empty-state client; and a specific-survey drill-down), not just statically reviewed.
