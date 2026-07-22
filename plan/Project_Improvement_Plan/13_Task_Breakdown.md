# 13 – Task Breakdown

> Developer-ready tasks formatted for Jira / ClickUp / Trello. Each task has a clear title, description, acceptance criteria, and estimate.

---

## Phase 1 Tasks

---

### TASK-001: Blank Survey Creation
**Type**: Feature | **Priority**: Critical | **Estimate**: 4h

**Description**: Allow creating a survey without selecting a template. Currently `SurveyService::createFromTemplate()` requires a template. Add a `createBlank()` method and update the create form.

**Acceptance Criteria**:
- [ ] "Start from scratch" option on survey create page
- [ ] Survey created with no questions
- [ ] `survey_template_id` is null
- [ ] Survey builder opens immediately after creation

**Files to modify**: `SurveyService`, `SurveyController`, `surveys/create.blade.php`

---

### TASK-002: Survey Duplication
**Type**: Feature | **Priority**: High | **Estimate**: 1 day

**Description**: Add a "Duplicate" action on the survey list. Deep-copy survey + all questions + logic rules + thank-you rules. New survey gets status `draft` and title `"Copy of {original title}"`.

**Acceptance Criteria**:
- [ ] Duplicate button on survey list row
- [ ] All questions copied with same settings/options/order
- [ ] All logic rules copied
- [ ] All thank-you rules copied
- [ ] New survey is in `draft` status
- [ ] New survey gets a new slug on publish

**Files to modify**: `SurveyService`, `SurveyController`, `surveys/index.blade.php`, `routes/admin.php`

---

### TASK-003: Question Duplication
**Type**: Feature | **Priority**: High | **Estimate**: 4h

**Description**: Add a "Duplicate" button on each question row in the survey builder. Creates a copy of the question with `order` = last + 1.

**Acceptance Criteria**:
- [ ] Duplicate button visible on each question
- [ ] Duplicated question appears at the bottom of the list
- [ ] All settings, options, and required flag are copied

**Files to modify**: `SurveyQuestionController`, `surveys/_questions-tab.blade.php`, `routes/admin.php`

---

### TASK-004: Drag-and-Drop Question Reorder
**Type**: Feature | **Priority**: Critical | **Estimate**: 2 days

**Description**: Replace up/down arrow buttons with drag-and-drop reordering using SortableJS. On drop, send a batch reorder request to the server.

**Acceptance Criteria**:
- [ ] Questions can be dragged and dropped to reorder
- [ ] Order is persisted to the database on drop
- [ ] Works for both survey questions and template questions
- [ ] Fallback: arrow buttons remain for accessibility

**Technical notes**:
- Add `POST /surveys/{survey}/questions/reorder` endpoint accepting `[{id, order}]` array
- Use SortableJS (already likely available via npm or CDN)

**Files to modify**: `SurveyQuestionController`, `surveys/_questions-tab.blade.php`, `routes/admin.php`

---

### TASK-005: Back Button in Multi-Step Surveys
**Type**: Feature | **Priority**: High | **Estimate**: 2 days

**Description**: Add a "Back" button to multi-step and conversational survey layouts. Store the current question position in the session/cookie so the user can go back and edit a previous answer.

**Acceptance Criteria**:
- [ ] "Back" button visible on all steps except the first
- [ ] Clicking back shows the previous question with the previously entered answer pre-filled
- [ ] Going back and changing an answer re-evaluates logic rules
- [ ] Works for multi_step and conversational layouts

**Files to modify**: `ResponseService`, `SurveyResponseController`, `survey/show.blade.php`, `survey/_question-frame.blade.php`

---

### TASK-006: Survey Expiry & Response Limit
**Type**: Feature | **Priority**: Medium | **Estimate**: 1 day

**Description**: Add `expires_at` (datetime) and `max_responses` (integer) to the surveys table. Check these in the response engine before allowing a new response.

**Acceptance Criteria**:
- [ ] Admin can set expiry date and max responses on survey settings
- [ ] Survey shows "unavailable" page if expired or response limit reached
- [ ] Expiry and limit shown on survey list

**Files to modify**: Migration, `Survey` model, `SurveyService`, `SurveyResponseController`, `surveys/edit.blade.php`

---

### TASK-007: Welcome Screen
**Type**: Feature | **Priority**: Medium | **Estimate**: 1 day

**Description**: Add a `welcome_screen` JSON column to surveys. If set, show a welcome page before the first question with title, description, and a "Start Survey" button.

**Acceptance Criteria**:
- [ ] Admin can configure welcome screen title and description
- [ ] Welcome screen shown before Q1 if configured
- [ ] "Start Survey" button advances to Q1
- [ ] Welcome screen respects survey theme

**Files to modify**: Migration, `Survey` model, `surveys/edit.blade.php`, `survey/show.blade.php`

---

### TASK-008: Embed Code UI
**Type**: Feature | **Priority**: High | **Estimate**: 4h

**Description**: Add an "Embed" section to the survey publish/share tab showing an iframe snippet that clients can copy-paste onto their website.

**Acceptance Criteria**:
- [ ] Embed code shown on survey share/publish tab
- [ ] One-click copy button
- [ ] Iframe dimensions configurable (width/height)
- [ ] Survey page handles iframe embedding (no X-Frame-Options block for own domain)

**Files to modify**: `surveys/_publish-tab.blade.php` or new `_share-tab.blade.php`

---

### TASK-009: reCAPTCHA v3 on Survey Submit
**Type**: Security | **Priority**: High | **Estimate**: 1 day

**Description**: Add Google reCAPTCHA v3 (invisible) to the survey submission flow. Verify the token server-side before processing the submit.

**Acceptance Criteria**:
- [ ] reCAPTCHA token generated on survey page load
- [ ] Token sent with submit request
- [ ] Server verifies token with Google API
- [ ] Submissions with score < 0.5 are rejected with 422
- [ ] Configurable via `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY` env vars
- [ ] Can be disabled per survey (for internal surveys)

**Files to modify**: `SurveyResponseController`, `survey/show.blade.php`, `config/services.php`

---

### TASK-010: GDPR Consent Checkbox
**Type**: Compliance | **Priority**: Critical | **Estimate**: 1 day

**Description**: Add a GDPR consent toggle to survey settings. When enabled, show a consent checkbox before submission with configurable text and a privacy policy link.

**Acceptance Criteria**:
- [ ] Admin can enable GDPR consent on survey settings
- [ ] Admin can set consent text and privacy policy URL
- [ ] Consent checkbox shown on survey before submit
- [ ] Submit blocked if consent not checked
- [ ] Consent recorded on the response

**Files to modify**: Migration (add `gdpr_enabled`, `gdpr_text`, `privacy_policy_url` to surveys), `Survey` model, `surveys/edit.blade.php`, `survey/show.blade.php`, `ResponseService`

---

### TASK-011: Performance Database Indexes
**Type**: Performance | **Priority**: Critical | **Estimate**: 4h

**Description**: Add missing indexes to `responses`, `response_answers`, `campaign_recipients`, `surveys`, and `short_links` tables.

**Acceptance Criteria**:
- [ ] Migration created and tested
- [ ] Analytics queries run in < 200ms on 10,000 responses
- [ ] No duplicate indexes

**Files to modify**: New migration file

---

### TASK-012: Redis Configuration
**Type**: Infrastructure | **Priority**: Critical | **Estimate**: 4h

**Description**: Configure Redis for cache, sessions, and queue. Update `.env.example` with Redis variables.

**Acceptance Criteria**:
- [ ] `CACHE_DRIVER=redis` working
- [ ] `SESSION_DRIVER=redis` working
- [ ] `QUEUE_CONNECTION=redis` working
- [ ] Analytics results cached for 5 minutes
- [ ] Cache invalidated on new response

**Files to modify**: `.env.example`, `config/cache.php`, `AnalyticsService`

---

### TASK-013: S3 Storage Configuration
**Type**: Infrastructure | **Priority**: High | **Estimate**: 4h

**Description**: Configure AWS S3 as the default filesystem. Update all file storage calls to use the configured disk.

**Acceptance Criteria**:
- [ ] `FILESYSTEM_DISK=s3` working
- [ ] Logo uploads stored in S3
- [ ] QR code files stored in S3
- [ ] Public URLs generated correctly
- [ ] Local storage still works as fallback in dev

**Files to modify**: `.env.example`, `config/filesystems.php`, `QrCodeService`, `ClientService`

---

### TASK-014: Usage Enforcement
**Type**: Feature | **Priority**: Critical | **Estimate**: 2 days

**Description**: Enforce subscription plan limits: `max_active_surveys`, `max_monthly_responses`, `max_monthly_campaign_sends`. Block actions when limits are reached.

**Acceptance Criteria**:
- [ ] Cannot publish a new survey if `max_active_surveys` reached
- [ ] Cannot record a new response if `max_monthly_responses` reached
- [ ] Cannot send a campaign if `max_monthly_campaign_sends` reached
- [ ] Clear error message shown to user when limit reached
- [ ] Usage stats visible on portal dashboard

**Files to modify**: `SurveyService`, `ResponseService`, `CampaignService`, new `UsageService`, portal dashboard

---

### TASK-015: Device/Browser/Source Charts
**Type**: Feature | **Priority**: Medium | **Estimate**: 1 day

**Description**: Add donut/bar charts for device type, browser, and response source to the analytics dashboard. Data is already stored in the `responses` table.

**Acceptance Criteria**:
- [ ] Device breakdown chart (mobile/desktop/tablet)
- [ ] Browser breakdown chart
- [ ] Source breakdown chart (direct/qr/sms/email/whatsapp)
- [ ] Charts update when date range filter changes

**Files to modify**: `AnalyticsService`, `analytics/dashboard.blade.php`

---

### TASK-016: Drop-Off Tracking
**Type**: Feature | **Priority**: High | **Estimate**: 1 day

**Description**: Track the last question answered on incomplete responses. Add `last_question_id` to `responses` table. Update `ResponseService::saveAnswer()` to set it.

**Acceptance Criteria**:
- [ ] `last_question_id` updated on every answer save
- [ ] Drop-off data available in `AnalyticsService`
- [ ] Drop-off funnel chart on analytics dashboard

**Files to modify**: Migration, `Response` model, `ResponseService`, `AnalyticsService`, `analytics/dashboard.blade.php`

---

### TASK-017: Audit Log UI
**Type**: Feature | **Priority**: Medium | **Estimate**: 1 day

**Description**: Add an admin page to view the Spatie activity log. Show: date, user, action, model, changes.

**Acceptance Criteria**:
- [ ] `/admin/audit-log` page accessible to `super_admin`
- [ ] Paginated list of log entries
- [ ] Filter by user, model type, date range
- [ ] Key models log their changes (Survey, Client, Campaign)

**Files to modify**: New `AuditLogController`, new view, `routes/admin.php`, add `LogsActivity` to key models

---

### TASK-018: Anonymous Response Toggle
**Type**: Feature | **Priority**: Medium | **Estimate**: 4h

**Description**: Add `is_anonymous` boolean to surveys. When enabled, skip collecting IP, device, browser, and contact attribution on responses.

**Acceptance Criteria**:
- [ ] Toggle in survey settings
- [ ] When enabled, response created without IP/device/browser
- [ ] Contact attribution skipped even if campaign link used
- [ ] Analytics shows "Anonymous mode enabled" notice

**Files to modify**: Migration, `Survey` model, `ResponseService`, `surveys/edit.blade.php`

---

## Phase 2 Tasks (Summary)

| Task ID | Title | Estimate |
|---|---|---|
| TASK-019 | Jump-to-page logic | 3 days |
| TASK-020 | OR condition in logic rules | 1 day |
| TASK-021 | End survey on condition | 4h |
| TASK-022 | Client user invite UI | 3 days |
| TASK-023 | Client user roles | 2 days |
| TASK-024 | Real-time analytics polling | 1 day |
| TASK-025 | Drop-off funnel chart | 1 day |
| TASK-026 | Geo analytics (IP lookup) | 2 days |
| TASK-027 | Webhooks (CRUD + delivery) | 3 days |
| TASK-028 | REST API v1 | 4 days |
| TASK-029 | API key management UI | 1 day |
| TASK-030 | Slack integration | 1 day |
| TASK-031 | GA / Meta Pixel on surveys | 4h |
| TASK-032 | Onboarding checklist | 2 days |
| TASK-033 | 2FA (TOTP) | 2 days |
| TASK-034 | Laravel Horizon | 4h |
| TASK-035 | Automated backups | 4h |

---

## Phase 3 Tasks (Summary)

| Task ID | Title | Estimate |
|---|---|---|
| TASK-036 | AI Survey Generator | 4 days |
| TASK-037 | AI Question Suggestions | 2 days |
| TASK-038 | AI Response Summary | 3 days |
| TASK-039 | Survey Quality Score | 2 days |
| TASK-040 | Zapier integration docs | 2 days |
| TASK-041 | Google Sheets integration | 3 days |
| TASK-042 | File upload question type | 4 days |
| TASK-043 | Image choice question type | 3 days |
| TASK-044 | Answer piping | 3 days |
| TASK-045 | Natural language report | 2 days |

---

## Phase 4 Tasks (Summary)

| Task ID | Title | Estimate |
|---|---|---|
| TASK-046 | NLP sentiment on text answers | 3 days |
| TASK-047 | AI Dashboard widget | 2 days |
| TASK-048 | Keyword extraction | 2 days |
| TASK-049 | Recommended actions | 2 days |
| TASK-050 | AI weekly digest email | 2 days |

---

## Phase 5 Tasks (Summary)

| Task ID | Title | Estimate |
|---|---|---|
| TASK-051 | Stripe billing | 5 days |
| TASK-052 | Subscription management UI | 3 days |
| TASK-053 | White-label / custom domain | 5 days |
| TASK-054 | Multi-language surveys | 5 days |
| TASK-055 | HubSpot integration | 5 days |
