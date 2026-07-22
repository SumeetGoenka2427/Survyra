# 14 – Implementation Checklist

> Actionable checklist organized by phase. Check off items as they are completed.

---

## Phase 1 – MVP Hardening

### Survey Builder
- [ ] TASK-001: Blank survey creation (no template required)
- [ ] TASK-002: Survey duplication
- [ ] TASK-003: Question duplication
- [ ] TASK-004: Drag-and-drop question reorder (SortableJS)
- [ ] TASK-005: Back button in multi-step surveys
- [ ] TASK-006: Survey expiry date and max response limit
- [ ] TASK-007: Welcome screen / intro page

### Sharing
- [ ] TASK-008: Embed code (iframe) UI on share tab
- [ ] Consolidate share tab: public link + short link + QR + embed in one place

### Security & Compliance
- [ ] TASK-009: reCAPTCHA v3 on survey submit
- [ ] TASK-010: GDPR consent checkbox on surveys
- [ ] Enable email verification for admin users (`MustVerifyEmail`)
- [ ] TASK-018: Anonymous response toggle
- [ ] Add security headers middleware (X-Frame-Options, X-Content-Type-Options, etc.)
- [ ] Sanitize `custom_css` field in SurveyTheme

### Performance & Infrastructure
- [ ] TASK-011: Add performance indexes migration
- [ ] TASK-012: Configure Redis (cache + session + queue)
- [ ] TASK-013: Configure S3 storage
- [ ] TASK-014: Enforce subscription plan limits (surveys, responses, campaigns)

### Analytics
- [ ] TASK-015: Device/browser/source charts
- [ ] TASK-016: Drop-off tracking (`last_question_id`)
- [ ] Drop-off funnel chart on analytics dashboard

### Admin
- [ ] TASK-017: Audit log UI (`/admin/audit-log`)
- [ ] Add `LogsActivity` to Survey, Client, Campaign models
- [ ] Add empty states to all list pages (surveys, campaigns, contacts, responses)
- [ ] Add loading spinners on all form submit buttons

---

## Phase 2 – High Priority Features

### Logic & Branching
- [ ] TASK-019: Jump-to-page / skip logic (`action: jump_to_question`)
- [ ] TASK-020: OR condition support in logic rules
- [ ] TASK-021: End survey on condition (`action: end_survey`)

### Team & Collaboration
- [ ] TASK-022: Client user invite UI (email invitation flow)
- [ ] TASK-023: Client user roles (owner/editor/viewer)

### Analytics
- [ ] TASK-024: Real-time analytics (30-second polling)
- [ ] TASK-025: Drop-off funnel chart
- [ ] TASK-026: Geo analytics (IP geolocation → country/city)
- [ ] Time-of-day heatmap

### Integrations
- [ ] TASK-027: Webhooks (CRUD UI + delivery job + retry)
- [ ] TASK-028: REST API v1 (surveys, responses, contacts)
- [ ] TASK-029: API key management UI (generate, revoke, copy)
- [ ] TASK-030: Slack notification integration
- [ ] TASK-031: Google Analytics + Meta Pixel on survey pages

### Security
- [ ] TASK-033: Two-factor authentication (TOTP)
- [ ] Add data retention scheduled command (`PurgeExpiredResponses`)
- [ ] Add privacy policy URL to survey settings

### Infrastructure
- [ ] TASK-034: Laravel Horizon setup
- [ ] TASK-035: Automated backups (spatie/laravel-backup → S3)

### UX
- [ ] TASK-032: Onboarding checklist for new clients
- [ ] Add breadcrumbs to all edit/detail pages
- [ ] Add "Quick Create Survey" button in top nav
- [ ] Add template preview modal (question list on hover)
- [ ] Add completion checkmarks to survey edit tabs
- [ ] Add "Survey Health" indicator on survey edit page
- [ ] Add "Send Now" button to scheduled reports
- [ ] Add sentiment filter chips to response list

---

## Phase 3 – Advanced Features

### AI
- [ ] TASK-036: AI Survey Generator (OpenAI GPT-4o-mini)
- [ ] TASK-037: AI Question Suggestions
- [ ] TASK-038: AI Response Summary (queued job + `ai_summaries` table)
- [ ] TASK-039: Survey Quality Score
- [ ] TASK-045: Natural language executive report

### Integrations
- [ ] TASK-040: Zapier integration documentation + testing
- [ ] TASK-041: Google Sheets integration (OAuth + sync job)
- [ ] Mailchimp / Brevo integration

### Question Types
- [ ] TASK-042: File upload question type (S3 storage)
- [ ] TASK-043: Image choice question type

### Survey Builder
- [ ] TASK-044: Answer piping (`{Q1}` variable in question text)
- [ ] Randomize question order option
- [ ] Randomize answer options order

### Analytics
- [ ] Cross-period comparison (this month vs. last month)
- [ ] NPS trend over time chart
- [ ] Question-level response rate

---

## Phase 4 – AI Features

- [ ] TASK-046: NLP sentiment analysis on text answers
- [ ] TASK-047: AI Dashboard widget (summary + keywords + recommendations)
- [ ] TASK-048: Keyword extraction from text responses
- [ ] TASK-049: Recommended actions based on metrics + AI analysis
- [ ] TASK-050: Automated weekly AI digest email to clients
- [ ] Duplicate question detection
- [ ] NPS benchmark comparison (industry averages)

---

## Phase 5 – Enterprise Features

- [ ] TASK-051: Stripe billing integration (Checkout + webhooks)
- [ ] TASK-052: Subscription management UI (upgrade/downgrade/cancel)
- [ ] TASK-053: White-label / custom domain per client
- [ ] TASK-054: Multi-language surveys (i18n)
- [ ] TASK-055: HubSpot CRM integration
- [ ] Razorpay payment integration
- [ ] SSO / OAuth login
- [ ] HIPAA compliance mode
- [ ] Custom subdomain per client

---

## Ongoing / Always-On

- [ ] Run `php artisan test` before every deploy
- [ ] Run `php artisan pint` (code style) before every commit
- [ ] Monitor Horizon dashboard for failed jobs
- [ ] Review Sentry errors weekly
- [ ] Review slow query log weekly
- [ ] Verify backup ran successfully daily
- [ ] Review audit log for suspicious activity weekly
- [ ] Update `plan/pending-work-master-plan.md` as tasks complete
