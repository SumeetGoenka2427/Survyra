# Implementation TODO

## Phase 2 — Critical Items

- [x] TASK-019: Jump-to-page / skip logic (✅ Already implemented in LogicEngine & ResponseService)
- [x] TASK-020: OR condition in logic rules (✅ Already implemented in LogicEngine)
- [x] TASK-021: End survey on condition (✅ Already implemented in LogicEngine & ResponseService)
- [x] TASK-027: Webhooks CRUD + delivery job (✅ Already implemented - WebhookService, DeliverWebhookJob, Portal WebhookController, webhooks.blade.php)
- [x] TASK-028: REST API v1 (✅ Already implemented - Api/SurveyController, with api_key middleware)
- [x] TASK-029: API key management UI (✅ Already implemented - ApiKeyController, api-keys.blade.php in portal)
- [x] TASK-022: Client user invite UI (✅ Already implemented - TeamController with invite/accept flow)
- [x] TASK-023: Client user roles (✅ Already implemented - owner/editor/viewer in ClientUser model)

## Phase 2 - Truly Missing

1. Migrations needed for new features
2. Two-Factor Authentication (TOTP)
3. Real-time analytics (polling)
4. Drop-off funnel chart widget
5. Geo analytics UI
6. Slack notification integration
7. GA/Meta Pixel injection in survey views
8. Onboarding checklist for new clients
9. Multi-language surveys

## Phase 2.5 - Missing UI/UX for existing features

1. Webhook delivery log viewer in portal
2. Webhook edit functionality (currently only create/delete)
3. Audit log for webhook events
4. API key last_used_at display  
5. Team member role change UI
6. Logic rule condition operator toggle UI improvement

## Phase 3 - Truly Missing

1. File upload question type
2. Image choice question type
3. AI Survey Generator
4. AI Question Suggestions
5. AI Response Summary
6. Survey Quality Score
7. Zapier integration
8. Google Sheets integration
9. Answer piping
10. Natural language executive report

## Phase 4 - Truly Missing

1. NLP sentiment on text answers
2. AI Dashboard widget
3. Keyword extraction
4. Recommended actions
5. AI weekly digest email