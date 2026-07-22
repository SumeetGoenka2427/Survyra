# 04 – Missing Features

> All missing features grouped by category with business justification, impact, complexity, and dependencies.

---

## Survey Builder

### 1. Drag-and-Drop Question Reorder
- **Why market expects it**: Every major platform uses drag-and-drop. Arrow buttons feel dated and slow.
- **Why it matters**: Survey creators reorder questions frequently during design. Arrow buttons require N clicks for N positions.
- **Business impact**: Directly affects daily usability for every client. High churn risk if UX feels inferior.
- **Complexity**: Medium – requires a JS sortable library (SortableJS) + a batch-reorder API endpoint.
- **Dependencies**: None.

### 2. Survey Duplication
- **Why market expects it**: Standard in all platforms. Clients create variations of the same survey.
- **Why it matters**: Saves time when creating seasonal or A/B variants.
- **Business impact**: Reduces time-to-survey for repeat users.
- **Complexity**: Easy – deep copy survey + questions + logic rules + thank-you rules.
- **Dependencies**: None.

### 3. Blank Survey Creation (No Template Required)
- **Why market expects it**: Templates are a starting point, not a requirement. Power users want a blank canvas.
- **Why it matters**: Agencies and consultants build custom surveys that don't fit any template.
- **Business impact**: Blocks a significant use case entirely.
- **Complexity**: Easy – allow `survey_template_id` to be null; skip template question seeding.
- **Dependencies**: None.

### 4. Question Duplication
- **Why market expects it**: Duplicating a question with its settings/options is faster than recreating it.
- **Business impact**: Minor but noticeable UX improvement.
- **Complexity**: Easy.
- **Dependencies**: None.

### 5. Welcome Screen / Intro Page
- **Why market expects it**: Typeform, Jotform, SurveyMonkey all support a branded intro before Q1.
- **Why it matters**: Sets context, increases completion rates, allows branding.
- **Business impact**: Higher completion rates = more data for clients.
- **Complexity**: Easy – add `welcome_screen` JSON column to `surveys` table.
- **Dependencies**: None.

### 6. Survey Expiry & Response Limit
- **Why market expects it**: Time-limited surveys (event feedback) and capped surveys (first 100 responses) are common.
- **Why it matters**: Clients need automatic survey closure without manual intervention.
- **Business impact**: Reduces admin overhead for clients.
- **Complexity**: Easy – add `expires_at` and `max_responses` to `surveys` table; check in response engine.
- **Dependencies**: None.

### 7. Back Button in Multi-Step Surveys
- **Why market expects it**: Every platform supports going back to edit a previous answer.
- **Why it matters**: Respondents make mistakes. No back button increases abandonment.
- **Business impact**: Directly impacts completion rate.
- **Complexity**: Medium – requires storing current step position in session/cookie.
- **Dependencies**: Response engine refactor.

### 8. Password-Protected Surveys
- **Why market expects it**: Internal surveys (HR, employee feedback) need access control.
- **Business impact**: Opens B2B internal use cases.
- **Complexity**: Easy – add `password` column to `surveys`; check on survey load.
- **Dependencies**: None.

### 9. Embed Code (iframe/Widget)
- **Why market expects it**: Embedding surveys on websites is a primary distribution channel.
- **Why it matters**: Clients want surveys on their own websites without redirecting users.
- **Business impact**: Significantly expands distribution reach.
- **Complexity**: Easy – generate iframe snippet; handle CORS headers.
- **Dependencies**: None.

---

## Question Types

### 10. File Upload Question
- **Why market expects it**: Jotform, Google Forms, Formstack all support it. Used for document collection, photo uploads.
- **Business impact**: Opens healthcare, legal, HR use cases.
- **Complexity**: Hard – requires S3/storage integration, file size limits, MIME validation.
- **Dependencies**: S3 storage setup.

### 11. Image Choice Question
- **Why market expects it**: Visual product feedback, brand preference surveys.
- **Business impact**: Differentiates for retail and marketing clients.
- **Complexity**: Medium – image upload per option + display in survey.
- **Dependencies**: Storage.

---

## Logic & Branching

### 12. Jump-to-Page / Skip Logic
- **Why market expects it**: Show/hide is not enough. Respondents should jump to a different section based on answers.
- **Why it matters**: Critical for branching surveys (e.g., "If Yes → go to Section 3").
- **Business impact**: Enables complex survey designs that clients currently can't build.
- **Complexity**: Medium – extend `SurveyLogicRule` with `action: jump_to_question`.
- **Dependencies**: Back button (for UX consistency).

### 13. OR Condition Support in Logic Rules
- **Why market expects it**: "Show if answer is A OR B" is a basic logic need.
- **Business impact**: Reduces the number of rules needed for common scenarios.
- **Complexity**: Medium – add `condition_operator` (AND/OR) to `survey_logic_rules`.
- **Dependencies**: None.

### 14. End Survey on Condition
- **Why market expects it**: Disqualify respondents who don't meet criteria (screener surveys).
- **Business impact**: Enables market research and screener use cases.
- **Complexity**: Easy – add `action: end_survey` to logic engine.
- **Dependencies**: None.

### 15. Answer Piping
- **Why market expects it**: "Hi {name}, how was your experience?" personalizes surveys.
- **Business impact**: Higher engagement and completion rates.
- **Complexity**: Hard – requires template variable parsing in question text.
- **Dependencies**: None.

---

## Sharing & Distribution

### 16. Embed Code UI
- **Why it matters**: Clients need a copy-paste snippet. Currently no UI exists.
- **Complexity**: Easy.
- **Dependencies**: None.

### 17. Survey Link Sharing UI
- **Why it matters**: No dedicated page to copy the survey URL, short link, or QR code together.
- **Complexity**: Easy – consolidate existing QR/short link into a "Share" tab.
- **Dependencies**: None.

---

## Analytics

### 18. Drop-off Analysis
- **Why market expects it**: SurveyMonkey and Typeform show where respondents abandon the survey.
- **Why it matters**: Identifies problematic questions that cause abandonment.
- **Business impact**: Helps clients improve survey quality and completion rates.
- **Complexity**: Medium – track `last_answered_question_id` on incomplete responses.
- **Dependencies**: Response engine update.

### 19. Device / Browser / Source Charts
- **Why it matters**: Data is already stored (`device`, `browser`, `source` columns) but no charts exist.
- **Complexity**: Easy – add chart components to analytics dashboard.
- **Dependencies**: None.

### 20. Geo Analytics (Country/City)
- **Why market expects it**: Understanding where respondents are from is standard.
- **Complexity**: Medium – IP geolocation lookup (MaxMind GeoLite2 or ip-api.com).
- **Dependencies**: IP stored on responses.

### 21. Real-Time Dashboard (Auto-Refresh)
- **Why market expects it**: Live response monitoring during events or campaigns.
- **Complexity**: Medium – polling or Laravel Echo + Pusher.
- **Dependencies**: None.

---

## AI Features

### 22. AI Survey Generator
- **Why market expects it**: Typeform and SurveyMonkey both offer AI-generated surveys.
- **Why it matters**: Reduces time-to-survey from hours to seconds.
- **Business impact**: Major differentiator for non-technical clients.
- **Complexity**: Hard – OpenAI API integration; prompt engineering.
- **Dependencies**: OpenAI API key.

### 23. AI Response Summary
- **Why market expects it**: Clients don't want to read 500 text responses manually.
- **Business impact**: Saves hours of analysis time per survey.
- **Complexity**: Hard – batch text answers → OpenAI summarization.
- **Dependencies**: OpenAI API.

### 24. Sentiment Analysis (NLP)
- **Why it matters**: Current sentiment is rule-based (score buckets). NLP on text answers would be far more accurate.
- **Complexity**: Hard – OpenAI or HuggingFace API per text answer.
- **Dependencies**: OpenAI API.

---

## Integrations

### 25. Webhooks
- **Why market expects it**: Every major platform supports webhooks. Enables custom automation.
- **Business impact**: Unlocks unlimited integration possibilities for technical clients.
- **Complexity**: Medium – `webhook_endpoints` table; fire HTTP POST on response completion.
- **Dependencies**: Queue.

### 26. REST API (Public)
- **Why market expects it**: Developers need programmatic access to surveys and responses.
- **Business impact**: Enables enterprise clients and agency workflows.
- **Complexity**: Medium – API routes with API key auth; rate limiting.
- **Dependencies**: API key management.

### 27. Zapier / Make Integration
- **Why market expects it**: Non-technical clients use Zapier to connect tools.
- **Business impact**: Connects Survyra to 5,000+ apps without custom code.
- **Complexity**: Medium – requires public REST API + webhook triggers.
- **Dependencies**: Webhooks, REST API.

### 28. Google Sheets Integration
- **Why market expects it**: Most SMBs use Google Sheets for data analysis.
- **Business impact**: Eliminates manual CSV export/import workflow.
- **Complexity**: Medium – Google Sheets API + OAuth.
- **Dependencies**: REST API.

### 29. Slack Notifications
- **Why market expects it**: Teams want real-time alerts in Slack on new responses.
- **Complexity**: Easy – Slack Incoming Webhook URL per client.
- **Dependencies**: None.

### 30. Google Analytics / Meta Pixel
- **Why market expects it**: Clients want to track survey page views and conversions.
- **Complexity**: Easy – add `ga_tracking_id` and `meta_pixel_id` to survey settings.
- **Dependencies**: None.

---

## Security & Compliance

### 31. GDPR Consent Checkbox
- **Why market expects it**: Required by law in EU. Jotform, Typeform, SurveyMonkey all have it.
- **Business impact**: Legal compliance for EU clients. Without it, platform is unusable in EU.
- **Complexity**: Easy – add consent question type or survey-level GDPR toggle.
- **Dependencies**: None.

### 32. reCAPTCHA / Bot Protection
- **Why market expects it**: Public surveys get spammed. Rate limiting alone is insufficient.
- **Business impact**: Protects data quality for all clients.
- **Complexity**: Easy – Google reCAPTCHA v3 on survey submit.
- **Dependencies**: None.

### 33. Two-Factor Authentication (2FA)
- **Why market expects it**: Standard security for SaaS platforms.
- **Business impact**: Reduces account takeover risk.
- **Complexity**: Medium – TOTP (Google Authenticator) via `pragmarx/google2fa-laravel`.
- **Dependencies**: None.

### 34. Audit Log UI
- **Why it matters**: `spatie/laravel-activitylog` is installed but there is no UI to view logs.
- **Complexity**: Easy – admin page to list activity log entries.
- **Dependencies**: None.

### 35. Anonymous Response Toggle
- **Why market expects it**: Many surveys should not collect IP/device/contact info.
- **Complexity**: Easy – add `is_anonymous` to `surveys`; skip IP/device/contact on response creation.
- **Dependencies**: None.

---

## Multi-Tenancy & Roles

### 36. Client User Management UI
- **Why it matters**: `ClientUser` model exists but there is no UI for clients to invite/manage team members.
- **Business impact**: Blocks team use cases entirely.
- **Complexity**: Medium – CRUD UI in portal; invite by email.
- **Dependencies**: None.

### 37. Client User Roles (Owner/Editor/Viewer)
- **Why it matters**: Different team members need different access levels.
- **Complexity**: Medium – extend `ClientUser` with a `role` column; gate portal actions.
- **Dependencies**: Client user management UI.

---

## Subscription & Billing

### 38. Usage Enforcement
- **Why it matters**: `max_active_surveys`, `max_monthly_responses`, `max_monthly_campaign_sends` are defined but never enforced.
- **Business impact**: Without enforcement, the subscription model has no teeth.
- **Complexity**: Medium – middleware/service checks before creating surveys, recording responses, sending campaigns.
- **Dependencies**: None.

### 39. Stripe Billing Integration
- **Why it matters**: Plans exist but there is no payment collection.
- **Business impact**: Platform cannot generate revenue without billing.
- **Complexity**: Hard – Stripe Checkout + webhooks + subscription lifecycle.
- **Dependencies**: Usage enforcement.

---

## Performance & Scalability

### 40. Database Indexes
- **Why it matters**: `responses`, `response_answers`, `campaign_recipients` will grow large. Missing indexes on `survey_id`, `client_id`, `status`, `started_at` will cause slow queries.
- **Complexity**: Easy – add index migrations.
- **Dependencies**: None.

### 41. Redis Caching
- **Why it matters**: Analytics queries are expensive. Caching with a short TTL would dramatically reduce DB load.
- **Complexity**: Easy – configure Redis; cache analytics results.
- **Dependencies**: Redis server.

### 42. S3 / Cloud Storage
- **Why it matters**: QR codes, logos, and future file uploads are stored locally. Local storage doesn't scale and is lost on server rebuild.
- **Complexity**: Easy – configure `FILESYSTEM_DISK=s3` in `.env`; update `filesystems.php`.
- **Dependencies**: AWS S3 bucket.

### 43. Laravel Horizon (Queue Monitoring)
- **Why it matters**: Campaign jobs run in the queue with no visibility into failures or throughput.
- **Complexity**: Easy – `composer require laravel/horizon`.
- **Dependencies**: Redis.
