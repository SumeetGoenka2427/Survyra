# UI/UX Modernization — Implementation Plan

## Context

`plan/redesign.md` is an aspirational, 30-point wishlist ("make it look like Typeform/Stripe/Linear, AJAX-ify everything, dark mode, drag-and-drop builder, global search, country maps, keyboard shortcuts...") covering the entire application. Taken literally and attempted all-at-once, it isn't buildable in a single pass — it spans a design system, a JS interaction layer, and every module built across Phases 1-6. This plan translates it into the same page-by-page, plan-then-build rhythm the rest of this project has followed, in the exact module order the redesign doc itself specifies (§9, final paragraph): **Dashboard → Clients → Surveys → Templates → Themes → Campaigns → Analytics → remaining modules.**

No business logic changes anywhere in this effort — every controller, service, route, and validation rule stays exactly as Phases 1-6 built it. This is UI/markup/CSS/JS only, plus the AJAX fragment endpoints needed to eliminate reloads (following the exact pattern Phase 4 and Phase 6 already established: server renders a Blade partial, JS swaps `innerHTML`).

## Constraints that shape scope

- **No build step.** Node v16 in this environment can't run modern Vite/Tailwind tooling (established back in Phase 1) — everything is CDN Bootstrap 5 + vanilla JS + Alpine.js already in use. The redesign stays in that stack: a hand-written design-system CSS file (CSS custom properties, utility/component classes layered over Bootstrap) rather than a Tailwind rebuild.
- **Charts**: ApexCharts (already introduced in Phase 6) is the one charting library, reused everywhere charts are requested rather than adding Chart.js too.
- **Trimmed / deferred from the wishlist** (flagging explicitly rather than silently dropping):
  - A full drag-and-drop survey builder rewrite (§12) is a large standalone project — the existing builder gets modernized styling, AJAX saves, and inline validation, but a full drag/drop rewrite is a separate follow-up, not bundled into this pass.
  - Country/location map charts (§6) — no geo/IP-location data is actually collected anywhere in the schema, so this stays a placeholder ("coming soon" empty state) rather than fabricated data.
  - Global `Ctrl+K` command-palette search (§20, §22) ships as a real cross-module search (clients/surveys/templates/campaigns) but a full command-palette UI is scoped as a fast-follow once the per-page AJAX search pattern is proven on one module.
  - Infinite scroll (§22) — AJAX pagination (already the established pattern) is used everywhere instead; infinite scroll is only added if a specific list turns out to need it.
  - Dark mode (§26/§27) ships as a real `data-bs-theme` toggle using Bootstrap 5.3's built-in dark mode variables — full custom dark-mode art direction for every custom component happens as each page is redesigned, not as a separate retrofit pass.

## Stage 0 — Design system foundation (this stage)

- `public/assets/css/design-system.css`: CSS custom properties for color scale (indigo/slate primary, emerald/orange/rose accents), spacing scale, radius scale, shadow scale, typography; component classes (`.ds-card`, `.ds-btn`, `.ds-badge`, `.ds-table`, `.ds-skeleton`, `.ds-empty-state`) layered on top of Bootstrap so existing `.btn`/`.card`/`.badge`/`.table` markup upgrades visually without a markup rewrite everywhere.
- `public/assets/js/toast.js`: a small `Toast.show(message, type)` helper (success/error/warning/info, auto-hide, top-right, animated) backing every AJAX success/error going forward, replacing the flash-message `<x-alert>` pattern for AJAX flows (full-page-load flashes still use `<x-alert>` for now-and-then form posts that haven't been AJAX-ified yet).
- Redesigned `resources/views/components/admin-layout.blade.php`: collapsible icon sidebar with section grouping and active-state highlighting, a real header bar (breadcrumb slot, quick-create dropdown, profile menu, dark-mode toggle), matching the Linear/Notion reference look.
- Matching (lighter-touch) polish to `portal-layout.blade.php` so the client portal doesn't look visually inconsistent with admin.
- New reusable Blade components: `x-ds-card`, `x-ds-empty-state`, `x-ds-skeleton-table`, toast container partial.

## Stage 1 — Dashboard

- KPI cards get icon + trend delta (computed from a `created_at` week-over-week comparison, not fabricated) + sparkline.
- "Recently Added Clients" table converted to an AJAX-refreshable widget (skeleton loading state on first paint, manual refresh button) — reuses Phase 6's fragment-JSON pattern.
- Empty state for zero-clients.

## Stages 2-7 (subsequent turns, same page-by-page order the redesign doc specifies)

2. **Clients** — stat strip (survey/campaign/response counts per client), searchable/filterable AJAX table, status toggle without reload.
3. **Surveys** — card-based list, status badges, AJAX publish/archive/delete.
4. **Templates** — category cards + grid/list toggle + AJAX duplicate/delete (the page in the screenshots).
5. **Themes** — gallery cards with live color/typography preview + AJAX apply/duplicate/delete (the page in the screenshots).
6. **Campaigns** — progress/performance cards, AJAX send/retry (already partially AJAX from Phase 5, extending to list-level actions).
7. **Analytics/Reports** — already fully AJAX from Phase 6; this stage is visual polish only (KPI card restyle, chart theming to match the new palette).

## Stage 8 — Remaining modules + polish pass

Auth pages (login/register/forgot/reset), 404/500 error pages, Settings/Users/Roles/Permissions/Logs if present, global search, keyboard shortcuts, dark-mode completeness pass across every page touched in Stages 1-7.

## Verification per stage

Same as every prior phase: manual browser walkthrough of the redesigned page(s) after each stage, confirming no regressions to existing business logic/tests (`php artisan test` stays green throughout — this work touches views/CSS/JS/fragment-returning controller actions, not services/models/validation).
