# Phase 7 — Reputation Management

## Context

`task.md`'s own Phase 7 roadmap (line ~1004) lists: dynamic thank-you rules, Google Review prompts, social redirects, complaint routing, review-click analytics. Most of that is **already built** — the master plan corrected the roadmap back in Phase 1 planning to move the Dynamic Thank-You Engine into Phase 3 (Survey Builder), because the blueprint's own workflow requires it before a survey can be published. `survey_thankyou_rules`, the sentiment-bucketed positive/neutral/negative screens, the Google Review/Facebook/website buttons, the complaint form, support-number and WhatsApp buttons are all live today (Phase 3), and covered by existing tests (e.g. "the negative thank-you rule can never have show_google_review enabled").

What's genuinely left from Phase 7, and what this doc scopes:
1. **Review-click analytics** — nothing currently tracks whether/how often a respondent actually clicks the Google Review, Facebook, website, WhatsApp, support-call, or complaint-form buttons on the thank-you screen. They're plain `<a href>` tags today.
2. **Module 12 — Notifications** — `task.md` gives this one word and no detail. The master plan's own elaboration (Phase 1 planning) scoped it as: in-app notifications for both admin and client portal, plus email alerts for two trigger events — negative feedback received, and campaign send completed. Neither exists yet. `User` and `ClientUser` already `use Notifiable` (added in Phase 1 anticipating this), but the `notifications` table migration was never created and no `Notification` classes exist.

This phase **adds real functionality**, unlike the UI-modernization tracks — flagging that up front per the master plan's own rule.

---

## 1. Database

- `notifications` — Laravel's standard notifications table (`id` uuid, `type`, `notifiable_type`, `notifiable_id`, `data` json, `read_at`, timestamps). Generated via `php artisan notifications:table`, not hand-rolled.
- `review_clicks` — `id`, `response_id` (FK → responses, cascade), `client_id` (FK, for fast per-client analytics without a join through responses→surveys), `channel` (string: `google_review` | `facebook` | `instagram` | `website` | `whatsapp` | `support_call` | `complaint_form`), `clicked_at`, timestamps.

No changes to any existing table.

## 2. Architecture decisions

- **Reuse Laravel's built-in notification system** — `Illuminate\Notifications\Notification` + the `database` and `mail` channels. No new package; `User`/`ClientUser` are already `Notifiable`. This mirrors the "don't introduce new machinery when the framework already has it" approach used throughout (e.g. Phase 6 reused Laravel's Mail facade rather than inventing an email layer).
- **Two notification classes**:
  - `NegativeFeedbackReceived` (survey, response) — dispatched to every `ClientUser` belonging to the response's client, `database` + `mail` channels. Mirrors the thank-you screen's own negative-sentiment bucket, so "what the respondent saw" and "who got alerted" always agree.
  - `CampaignSendCompleted` (campaign) — dispatched to the internal `User` who created the campaign, `database` + `mail` channels.
- **Trigger points, not new events/observers** — both notifications fire from the exact place the state transition already happens, avoiding a new event-listener layer for two call sites:
  - `ResponseService::submit()` (`app/Services/ResponseService.php`), right after `$response->update([..., 'sentiment' => $rule->sentiment])`: if `$rule->sentiment === 'negative'`, notify the client's users.
  - `CampaignService::refreshStats()` (`app/Services/CampaignService.php`), inside the existing `if ($counts->except(['pending'])->sum() === $campaign->recipients()->count())` block that already flips status to `completed`: notify `$campaign->createdBy` (or whoever the `created_by` user is).
- **Review-click tracking is a redirect, not a beacon.** Replacing the thank-you screen's direct `<a href="{{ $survey->client->google_review_url }}">` links with links to a new public, unauthenticated route `GET /r/{response:uuid}/{channel}` that logs a `review_clicks` row then issues a real `302` redirect to the actual target URL (Google review URL, Facebook URL, website, `wa.me/...`, `tel:...`, `mailto:...`). This works with JavaScript disabled, needs no client-side tracking code, and matches the existing public-survey routes' "plain, unauthenticated, minimal" style (same family as `/s/{slug}`). A single controller method handles all six channels via a small match expression resolving `channel → target URL` from the response's client.
- **In-app notification bell** — one shared Blade partial + AJAX endpoint pattern (matches every fragment-swap built in the redesign tracks), one for the admin header, one for the portal header, each hitting its own guard-scoped route (`admin.notifications.*` / `portal.notifications.*`) since admin and portal notifications belong to different `Notifiable` models on different guards — never cross-visible, consistent with every other dual-guard rule in this app.
- **No new Settings toggle for opting out of email alerts in this pass** — Module 11 (Settings) already has a generic key-value store from Phase 1; wiring a per-client "mute negative-feedback emails" toggle is a small, real, separate feature and is called out as a fast-follow rather than bundled in silently.

## 3. Implementation breakdown

**Migrations**: `notifications` (via `php artisan notifications:table`), `create_review_clicks_table`.

**Models**: `ReviewClick` (belongsTo Response, Client).

**Notifications**: `app/Notifications/NegativeFeedbackReceived.php`, `app/Notifications/CampaignSendCompleted.php` — each implementing `toMail()` (reusing the existing Markdown mail component pattern from Phase 6's `ScheduledReportMail`) and `toArray()` (for the database channel — survey title, response id/link, sentiment or campaign name/stats).

**Services**: `ReviewClickService::log(Response $response, string $channel): string` (logs the click, returns the resolved redirect target — keeps the controller thin and the channel→URL resolution unit-testable in isolation).

**Controllers**:
- `App\Http\Controllers\Public\ReviewClickController@redirect` — the tracked-redirect endpoint.
- `App\Http\Controllers\Admin\NotificationController` — `index` (AJAX fragment, unread + recent), `markRead`.
- `App\Http\Controllers\Portal\NotificationController` — same, scoped to the authenticated `ClientUser`.

**Views**: `survey/_thankyou-frame.blade.php` updated so every outbound button routes through `/r/{response}/{channel}` instead of a direct external link (zero visual change, respondents never notice). Shared `resources/views/notifications/_bell.blade.php` partial (dropdown, unread badge) included from both `admin-layout.blade.php` and `portal-layout.blade.php` headers — the bell icon slot the redesign's Stage 0 header already anticipated but left empty.

**Analytics integration**: `AnalyticsService::forClient()` gains a `review_clicks` breakdown (count per channel) in the returned snapshot, rendered as a small card/table in `analytics/dashboard.blade.php` — reuses the exact snapshot-shape/AJAX-refresh pattern Phase 6 already built, no new endpoints needed beyond what `analytics.data` already serves.

**Routes**: `GET /r/{response}/{channel}` (public, no auth — same middleware group as `/s/{slug}`); `admin.notifications.index`/`admin.notifications.mark-read`; `portal.notifications.index`/`portal.notifications.mark-read`.

## 4. Tests (Pest)

- Completing a survey with a negative-sentiment thank-you rule queues a `NegativeFeedbackReceived` notification to every `ClientUser` of that client (`Notification::fake()`), and not to unrelated clients' users.
- A campaign reaching 100% processed recipients (via `refreshStats()`) queues exactly one `CampaignSendCompleted` notification to its creator, not on every partial `refreshStats()` call before completion.
- Hitting `/r/{response}/{channel}` for each of the six channels logs a `review_clicks` row with the right channel and redirects (`302`) to the correct external target built from that response's client fields; an unknown channel or a response `uuid` that doesn't exist returns 404, not a fatal error.
- The notification bell's AJAX endpoint returns only the authenticated admin/portal user's own notifications (never another tenant's or another guard's), and mark-read flips `read_at` and removes it from the unread badge count.
- Existing thank-you-rule tests stay green untouched — proves the redirect-wrapper change is visually and behaviorally invisible to the respondent flow.

## 5. Verification

- `php artisan test` — new Phase 7 suite green alongside the full existing suite (112 tests as of the last redesign-track pass).
- Manual walkthrough: submit a real negative-sentiment response on Demo Cafe's survey → confirm a database notification appears in the portal bell for Demo Cafe's owner and an email lands (log driver) → click the "Leave a Google Review" button on the thank-you screen → confirm it actually redirects to the client's real Google Review URL and a `review_clicks` row is created → check the Analytics dashboard shows the click in the new breakdown → run a campaign to completion → confirm the creating admin gets a database + email notification.

---

## Phase 7 status: DONE

Built exactly per the plan above: `notifications` + `review_clicks` migrations, `ReviewClick` model, `ReviewClickService` (channel → target-URL resolution for all 6 real thank-you-screen actions: google_review, facebook, website, complaint_form, support_call, whatsapp — no "instagram" channel exists since the actual `_thankyou-frame.blade.php` never had an Instagram button, unlike this doc's earlier draft list), `Public\ReviewClickController` + `/r/{response}/{channel}` route, `NegativeFeedbackReceived` + `CampaignSendCompleted` notifications wired into the exact hook points named above, a shared `notifications/_bell.blade.php` + `_items.blade.php` partial included in both `admin-layout` and `portal-layout` headers, `Admin`/`Portal` `NotificationController::markRead()`, and a "Review & Action Clicks" card in the shared analytics dashboard. 6 new Pest tests, 118 total passing.

Real bugs/gaps found and fixed during this phase:

1. **`portal-layout.blade.php` had never received Stage 0's redesign at all.** While wiring the notification bell into the portal header, discovered the portal layout was still the original plain Bootstrap navbar linking only `assets/css/app.css` — the redesign's own Stage 0 note claiming "lighter-touch polish already applied" was inaccurate. Fixed alongside this phase's work: added `design-system.css`, a branded icon+text navbar-brand, and the shared toast/keyboard-shortcut script (`admin-shell.js`, reused as-is since every admin-specific selector inside it is null-checked and simply no-ops on portal pages).
2. **Wrong route name on the first pass.** The tracked-redirect route (`/r/{response}/{channel}`) is declared outside the `survey.`-prefixed route group (matching the existing `short-link.redirect` convention), so its real name is `review-click.redirect`, not `survey.review-click.redirect`. Caught immediately by the full test suite (`Route [survey.review-click.redirect] not defined`) before any manual testing.
3. **Real notifications are queued, not synchronous, outside of tests.** `QUEUE_CONNECTION=database` in the actual `.env` (vs `sync` in `phpunit.xml`) meant the manual-verification negative-feedback notification didn't appear until `php artisan queue:work --stop-when-empty` was run — not a bug, the same operational note Phase 5 already flagged for campaign sending, now also true for notifications.
4. **Double-notification guard on campaign completion.** `CampaignService::refreshStats()` can be called multiple times as recipients trickle in; without checking whether the campaign was *already* `completed` before this call, a second `refreshStats()` call after completion (e.g. from a retry) would re-fire `CampaignSendCompleted`. Fixed by capturing `$wasAlreadyCompleted` before the update and only notifying on the actual not-completed → completed transition.

No deviations from the plan's scope — dynamic thank-you rules, Google Review prompts, social redirects, and the complaint form were confirmed already built in Phase 3 and untouched here; this phase was exactly the two gaps it set out to close.
