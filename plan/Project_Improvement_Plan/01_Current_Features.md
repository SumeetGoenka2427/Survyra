# 01 – Current Features Inventory

> Based on actual code inspection of models, services, controllers, routes, views, and migrations.

---

## Feature Inventory Table

| Feature Name | Status | Module | User Role | Description | Completion % | Priority |
|---|---|---|---|---|---|---|
| Admin Authentication | Complete | Auth | Admin | Login, logout, forgot/reset password via `web` guard | 100% | Critical |
| Client Portal Authentication | Complete | Auth | Client | Separate `client` guard login, forgot/reset password | 100% | Critical |
| Role & Permission System | Complete | Auth | Admin | Spatie `laravel-permission` with roles: `super_admin`, `survyra_admin`; permissions: `manage-surveys`, `send-campaigns`, `view-analytics` | 100% | Critical |
| Admin Dashboard | Complete | Dashboard | Admin | Recent clients fragment, stats overview | 85% | High |
| Client Management (CRUD) | Complete | Admin | Admin | Create/edit/delete/toggle-status clients with subscription plan assignment | 100% | High |
| Client Profile (Self-Manage) | Complete | Portal | Client | Client portal users can update logo, phone, website, social URLs, support/WhatsApp numbers | 90% | High |
| Subscription Plans | Complete | Admin | Admin | Plans with `max_active_surveys`, `max_monthly_responses`, `max_monthly_campaign_sends`, price, billing cycle | 70% | High |
| Survey Templates (Admin) | Complete | Templates | Admin | Create/edit/delete/duplicate templates with industry category, layout, questions | 100% | High |
| Survey Template Questions | Complete | Templates | Admin | Add/edit/delete/reorder questions within templates | 100% | High |
| Survey Themes | Complete | Themes | Admin | System and client-scoped themes: logo, colors, background, font, button style, progress bar, border radius, custom CSS | 95% | High |
| Survey Builder | Complete | Surveys | Admin | Create surveys from templates; add/edit/delete/reorder questions; publish/archive/delete | 95% | Critical |
| Survey Layouts | Complete | Surveys | Admin/Client | `multi_step`, `one_page`, `card_based`, `section_wizard`, `conversational` layouts | 100% | High |
| Survey Logic Rules | Complete | Surveys | Admin | Conditional show/hide questions based on answers (LogicEngine with 7 operators) | 90% | High |
| Survey Thank-You Rules | Complete | Surveys | Admin | Sentiment-based (positive/neutral/negative) thank-you pages with score buckets; show/hide review links, complaint form, WhatsApp, coupon | 95% | High |
| Survey Publishing | Complete | Surveys | Admin | Publish (generates slug), archive; draft-only deletion guard | 100% | Critical |
| Survey QR Codes | Complete | Surveys | Admin | Generate labeled QR codes (PNG/SVG) linked to short links; download as PDF | 90% | High |
| Short Links | Complete | Surveys | Admin | Auto-generated short links (`/l/{code}`) with click tracking | 95% | High |
| Public Survey Response Engine | Complete | Survey | Public | Multi-step, one-page, card-based, section-wizard, conversational response flows; cookie-based resume | 95% | Critical |
| Answer Validation | Complete | Survey | Public | Per-question-type validation rules enforced server-side | 100% | Critical |
| Survey Scoring | Complete | Survey | Public | Per-answer score computed by question type contract; primary score question drives sentiment | 100% | High |
| Sentiment Classification | Complete | Survey | Public | Score-to-sentiment mapping via thank-you rules; negative triggers notification | 100% | High |
| Negative Feedback Notification | Complete | Notifications | Client | In-app + email notification to client users on negative response | 90% | High |
| Campaign Send Completed Notification | Complete | Notifications | Admin | Notification to campaign creator when send job finishes | 90% | Medium |
| Contact Management | Complete | Contacts | Admin | CRUD contacts per client; encrypted phone/email; consent flag; tags | 95% | High |
| Contact Tags | Complete | Contacts | Admin | Tag contacts for campaign targeting | 90% | High |
| Contact Import (CSV/Excel) | Complete | Contacts | Admin | Bulk import via `maatwebsite/excel` with `ContactsImport` | 85% | High |
| Campaign Manager | Complete | Campaigns | Admin | Create campaigns targeting contacts by tag; send via job queue; retry failed; stats refresh | 90% | High |
| Campaign Providers | Complete | Campaigns | Admin | Registry-based provider system (`CampaignProviderRegistry`); config-driven (`campaign_providers.php`) | 80% | High |
| Campaign Jobs | Complete | Campaigns | Admin | `SendCampaignJob` → `SendCampaignMessageJob` per recipient | 90% | High |
| Analytics Dashboard (Admin) | Complete | Analytics | Admin | Total/today responses, completion rate, avg completion time, sentiment counts, NPS/CSAT/CES/Rating metrics, question breakdown, trend chart, review click breakdown | 90% | High |
| Analytics Dashboard (Portal) | Complete | Analytics | Client | Same analytics engine scoped to client's own data | 90% | High |
| Response Viewer | Complete | Analytics | Admin/Client | Paginated response list + individual response detail with all answers | 85% | High |
| Scheduled Reports | Complete | Reports | Admin/Client | Weekly/monthly/quarterly email reports via `ScheduledReportMail`; PDF export via DomPDF | 85% | High |
| Response Export | Complete | Analytics | Admin/Client | Export responses to CSV/Excel via `ResponsesExport` | 85% | Medium |
| Reputation Management | Complete | Survey | Public | Post-survey review routing: Google, Facebook, website, complaint form, support call, WhatsApp; click tracking via `ReviewClickService` | 95% | High |
| Review Click Analytics | Complete | Analytics | Admin/Client | Per-channel review click counts in analytics dashboard | 90% | High |
| Activity Log | Complete | Admin | Admin | Spatie `laravel-activitylog` installed and migrated | 60% | Medium |
| Question Types (19 types) | Complete | Builder | Admin | NPS, CSAT, CES, Rating Stars, Emoji Rating, Slider, Checkbox, Radio, Dropdown, Textbox, Textarea, Email, Phone, Number, Date, Yes/No, Matrix, Ranking, Yes/No | 100% | Critical |
| Question Styles (multi-style) | Complete | Builder | Admin | NPS: numbers/emoji/circles/gradient; CSAT/CES: circles/gradient/numbers; Checkbox/Radio/Dropdown: multiple styles; Rating Stars: stars/hearts/numbers; etc. | 95% | High |
| Survey Preview | Complete | Admin | Admin | Live preview of survey rendering | 80% | Medium |
| Admin Search | Complete | Admin | Admin | Global search endpoint | 70% | Medium |
| Profile Management | Complete | Auth | Admin/Client | Update name/email/password for both guards | 100% | Medium |
| Error Pages | Complete | UI | All | Custom 403/404/500 pages | 100% | Low |
| PDF QR Code Download | Complete | Surveys | Admin | QR code rendered to PDF via DomPDF | 90% | Medium |

---

## Installed Packages

| Package | Purpose |
|---|---|
| `laravel/breeze` | Auth scaffolding |
| `spatie/laravel-permission` | Roles & permissions |
| `spatie/laravel-activitylog` | Audit trail |
| `maatwebsite/excel` | CSV/Excel import/export |
| `barryvdh/laravel-dompdf` | PDF generation |
| `simplesoftwareio/simple-qrcode` | QR code generation |
| `intervention/image` | Image processing |

---

## Architecture Summary

- **Multi-guard auth**: `web` (admin `User`) + `client` (portal `ClientUser`)
- **Repository pattern**: `SurveyRepository`, `ClientRepository`, `SurveyTemplateRepository`
- **Service layer**: 18 services covering all business logic
- **Question type registry**: Contract-based, config-driven (`question_types.php`)
- **Campaign provider registry**: Config-driven (`campaign_providers.php`)
- **Queue**: Laravel jobs for campaign sending
- **Soft deletes**: Surveys, Clients, Contacts
- **Encryption**: Contact phone/email, Client support/WhatsApp numbers
- **UUID**: Responses, Clients
