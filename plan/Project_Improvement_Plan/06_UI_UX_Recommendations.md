# 06 – UI/UX Recommendations

> Page-by-page review with specific improvement suggestions and rationale.

---

## Navigation

**Issues:**
- No breadcrumbs on deep pages (survey edit → questions tab).
- No active state indicator in sidebar for sub-pages.
- Admin and portal sidebars may not be consistent in structure.

**Recommendations:**
1. Add breadcrumb component to all edit/detail pages.
2. Highlight active sidebar item including sub-routes.
3. Add keyboard shortcut hints (e.g., `?` for help overlay).
4. Add a "Quick Create Survey" button in the top nav bar.

**Why**: Navigation clarity reduces cognitive load and support requests.

---

## Survey Creation Flow

**Issues:**
- Users must pick a template before creating a survey — no blank option.
- No preview of template questions before selecting.
- No indication of how many questions a template has.

**Recommendations:**
1. Add "Start from scratch" option alongside templates.
2. Show template question count and a preview tooltip on hover.
3. Add template categories/filter (by industry, by type: NPS/CSAT/etc.).
4. Add a "Recently used templates" section.

**Why**: Forcing template selection blocks power users and slows down experienced clients.

---

## Survey Builder / Editor

**Issues:**
- Question reorder requires multiple arrow-button clicks.
- No visual feedback when a question is saved.
- No undo/redo.
- Logic rules UI is separate from questions — hard to see which questions have logic.
- No inline preview of how a question will look.

**Recommendations:**
1. Implement drag-and-drop reorder (SortableJS).
2. Show a "Saved ✓" toast after each question update.
3. Add a logic indicator badge on questions that have rules.
4. Add inline mini-preview per question type.
5. Add "Duplicate question" button.
6. Add question search/filter for surveys with many questions.

**Why**: Builder UX is the most-used feature. Every friction point here multiplies across all clients.

---

## Survey Editing (Tabs)

**Issues:**
- Tab layout (Questions / Logic / Theme / Thank You / Publish / QR) is good but tabs have no completion indicators.
- "Publish" tab is the last tab — users may not find it.
- No warning when navigating away with unsaved changes.

**Recommendations:**
1. Add completion checkmarks to tabs (e.g., "Theme ✓", "Thank You ✓").
2. Move "Publish" to a prominent button in the header, not a tab.
3. Add unsaved-changes warning on navigation.
4. Add a "Survey Health" indicator (e.g., "3 required questions, logic configured, theme set").

**Why**: Reduces incomplete survey configurations and support tickets.

---

## Dashboard

**Issues:**
- Admin dashboard shows recent clients but no platform-wide stats (total surveys, total responses, active campaigns).
- Portal dashboard is analytics-only — no quick actions.

**Recommendations:**
1. Admin dashboard: add platform KPIs (total clients, total responses today, active campaigns, revenue).
2. Portal dashboard: add "Quick Actions" panel (Create Survey, View Responses, Send Campaign).
3. Add "What's New" or onboarding checklist for new clients.
4. Add sparkline trend on stat cards.

**Why**: Dashboards should answer "what's happening right now" at a glance.

---

## Analytics

**Issues:**
- No charts for device, browser, or source breakdown (data exists in DB).
- No drop-off funnel visualization.
- Date range picker may not be intuitive.
- Question breakdown shows raw data but no visual charts.

**Recommendations:**
1. Add donut/bar charts for device, browser, source.
2. Add a funnel chart showing response drop-off per question.
3. Add a date range preset selector (Today / Last 7 days / Last 30 days / Custom).
4. Add bar/pie charts for choice questions in question breakdown.
5. Add a "Compare periods" toggle (this month vs. last month).

**Why**: Visual analytics are the primary value delivery mechanism for clients.

---

## Response Review

**Issues:**
- Response list shows basic info but no quick-filter by sentiment.
- Individual response detail is functional but plain.
- No way to add notes/tags to a response.

**Recommendations:**
1. Add sentiment filter chips (Positive / Neutral / Negative) to response list.
2. Add source and device filter.
3. Add response detail export (single response PDF).
4. Add internal notes field on response detail (admin/client only).

**Why**: Clients review negative responses most urgently — filtering by sentiment saves time.

---

## Templates

**Issues:**
- Template list shows name and category but no question count or preview.
- No way to filter templates by industry or question type.

**Recommendations:**
1. Show question count on template cards.
2. Add a "Preview" modal showing all template questions.
3. Add industry filter and search.
4. Add "Popular" and "New" badges.

**Why**: Better template discovery increases template adoption and reduces blank-survey creation time.

---

## Settings / Company Profile

**Issues:**
- Company profile and personal profile are separate pages — could be unified.
- No visual feedback on logo upload success.
- Brand color picker has no preview of how it looks on a survey.

**Recommendations:**
1. Add a live brand color preview showing a sample survey button/header.
2. Show logo preview immediately after upload.
3. Add a "Preview your survey branding" link.

**Why**: Visual feedback reduces errors and increases confidence in settings.

---

## Admin Panel

**Issues:**
- Client list is a table — no card view option for visual scanning.
- No bulk actions (bulk activate/deactivate clients).
- No client health indicators (last active, response count, plan usage).

**Recommendations:**
1. Add client health columns: last survey created, total responses, plan usage %.
2. Add bulk status toggle.
3. Add client search with filters (by plan, by status, by industry).
4. Add a "Client Overview" quick-view panel on row click.

**Why**: As the platform scales, admins need efficient client management tools.

---

## Reports

**Issues:**
- Report creation form is minimal — no preview of what the report will contain.
- No way to manually trigger a report send.
- No report history (when was it last sent, to whom).

**Recommendations:**
1. Add "Send Now" button to manually trigger a report.
2. Show last sent date and recipient list on report row.
3. Add a report preview modal.

**Why**: Clients need confidence that reports are working before relying on them.

---

## Mobile Experience

**Issues:**
- Admin panel likely not optimized for mobile (complex tables, multi-tab editors).
- Survey response pages should be mobile-first.

**Recommendations:**
1. Ensure survey response pages are fully responsive and touch-friendly.
2. Make question option buttons large enough for touch (min 44px tap target).
3. Admin panel: at minimum, make dashboard and analytics readable on mobile.
4. Test all survey layouts on iOS Safari and Android Chrome.

**Why**: A significant portion of survey respondents are on mobile. Poor mobile UX kills completion rates.

---

## Accessibility

**Recommendations:**
1. All form inputs must have associated `<label>` elements.
2. Color contrast must meet WCAG AA (4.5:1 for text).
3. Survey navigation must be keyboard-accessible (Tab, Enter, Arrow keys).
4. Add `aria-label` to icon-only buttons.
5. Add `role="alert"` to validation error messages.

**Why**: Accessibility is a legal requirement in many markets and improves usability for all users.

---

## Loading Performance

**Issues:**
- No skeleton loaders on data tables (only `ds-skeleton-table` component exists — check usage).
- Analytics page may load slowly with large datasets.

**Recommendations:**
1. Implement skeleton loaders on all data tables and stat cards.
2. Paginate or lazy-load question breakdown on analytics.
3. Cache analytics results with a 5-minute TTL.
4. Add loading spinners on all form submit buttons.

---

## Empty States

**Recommendations:**
1. Every list page (surveys, campaigns, contacts, responses) must have a meaningful empty state with a CTA.
2. Empty analytics dashboard should show "No responses yet — share your survey" with a link.
3. Empty template list should prompt admin to create the first template.

**Why**: Empty states guide users to the next action instead of leaving them confused.

---

## Error Messages

**Recommendations:**
1. Validation errors should appear inline next to the field, not only at the top.
2. API errors should show a user-friendly message, not a raw exception.
3. 404 page should suggest navigation options.
4. Failed campaign sends should show a clear retry CTA.

---

## Onboarding

**Recommendations:**
1. New client first login: show a 3-step checklist (Complete profile → Create first survey → Share survey).
2. Add tooltips on first visit to key UI elements (survey builder, logic rules, thank-you rules).
3. Add a "Getting Started" video or walkthrough link in the portal dashboard.

**Why**: Onboarding directly impacts activation rate (% of clients who create their first survey).
