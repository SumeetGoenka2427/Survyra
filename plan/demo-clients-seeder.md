# Demo Clients Seeder

## Purpose

The platform had one skeleton demo client ("Demo Cafe", from `DemoDataSeeder`) with no survey, no responses, and no campaign data — nothing to actually explore. This adds `database/seeders/DemoClientsSeeder.php`, run after `SurveyTemplateSeeder` in `DatabaseSeeder`, to seed 5 realistic clients across 5 different industries so every built feature (dashboards, analytics, reputation management, campaigns, the new question types) has real, varied data behind it.

## What it builds, per client

| Client | Industry | Template | Theme | Bonus question |
|---|---|---|---|---|
| Sunrise Family Clinic | Healthcare | Patient Satisfaction | Healthcare | — |
| Spice Route Bistro | Restaurant | Dining Experience | Cafe | Ranking (rank aspects by importance) |
| CloudDesk Support | Customer Support | CSAT Survey | Corporate | Slider (0-10 support experience) |
| Bright Minds Institute | Education | Course Feedback | Modern | — |
| UrbanStyle Retail Co. | Retail | Store Experience | Minimal | Matrix (rate 4 store aspects) |

Each client gets: a `client_users` owner login (`owner@{slug}.test` / `password`), a published survey (via the real `SurveyService::createFromTemplate()` + `publish()` path, not hand-crafted rows), 30-50 contacts, one completed campaign (sms/whatsapp/email mix) with realistic delivered/sent/failed recipient statuses, and 40-65 responses spread over the last 90 days with a ~63/24/13 positive/neutral/negative sentiment split.

Three of the five clients got one extra, non-required question appended directly to their published survey (Ranking, Slider, Matrix respectively) — deliberately covering all 3 net-new question types with real seeded data, not just Pest-test fixtures.

## How response data is generated

For each response, a sentiment bucket (positive/neutral/negative) is picked first, then every question on the survey gets a bucket-consistent answer via a per-question-type generator (e.g. NPS 9-10 for positive/0-6 for negative, radio/dropdown prefers "Yes" for positive and "No" for negative when those options exist, textarea picks from a small pool of realistic canned comments per bucket). Each answer's `score` is computed by calling the question's own `QuestionTypeContract::score()` — the same scoring logic real submissions use — rather than re-deriving it by hand, so seeded data can never drift from actual scoring behavior. The response's overall `score`/`sentiment` are taken from the survey's `primary_score_question_id` answer.

A subset of responses are linked to actual campaign recipients (`contact_id`/`campaign_id`/`source` set to match), so campaign-attribution analytics has real data too. A handful of negative responses per client fire a real `NegativeFeedbackReceived` notification via `Notification::sendNow()` (bypassing the queue so the portal notification bell has live rows without needing a queue worker), and roughly a quarter of positive responses get a `ReviewClick` row.

## Status: DONE

Verified via `php artisan migrate:fresh --seed` (all 8 seeders run clean) plus a full curl walkthrough: all 5 seeded public survey pages return 200 with no error markers; admin login → dashboard → clients list shows all 5 companies; analytics and campaigns pages render for a seeded client with no errors; `admin.survey-preview` correctly renders the seeded Matrix question in its own step with the `table` style; the template builder edit page lists "Matrix (Rate Multiple Rows)"; portal login as a seeded owner works and the dashboard's notification bell shows a real "Negative feedback received" row. Final counts from a fresh seed: 6 clients, 5 surveys, 268 responses, 1,382 answers, 5 campaigns, 200 recipients, 200 contacts, 43 review clicks, 25 notifications. Full Pest suite stays green (155 passing) since the test database is a separate in-memory SQLite connection this seeder never touches.
