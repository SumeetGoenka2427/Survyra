# Phase 5 — Campaign Manager

## Context

Phases 1–4 built everything needed to configure and collect a survey, but there's still no way to actually *reach* a customer other than manually handing them a link. Phase 5 is Module 9 (`task.md` §17: Campaign Management) and Module 18's Contact Management — SMS/WhatsApp/Email sending, contact/tag management, delivery and click tracking, and the full persisted QR system that Phase 3 deliberately shipped only an ad-hoc version of.

This phase also picks up two things earlier phases explicitly deferred for exactly this moment: `responses.contact_id` and `responses.campaign_id` (Phase 4 gap), and the labeled, multi-format, persisted QR system (Phase 3 gap — that phase only needed "a QR so I can share this survey right now").

**A hard constraint shapes almost every decision below**: this environment has no real SMS/WhatsApp provider account, no DLT-registered SMS templates, and no Meta-approved WhatsApp templates. That isn't a code gap — it's the same external, non-code blocker the master plan flagged back in gap #9. Nothing here can "just send a real SMS" regardless of how well it's built. So this phase builds the entire pipeline against a provider abstraction with a working default that logs instead of calling a real API — same pattern Laravel's own `MAIL_MAILER=log` already uses elsewhere in this app — so it's fully demonstrable and testable now, and becomes real the moment the user supplies actual provider credentials later.

---

## 1. Scope (from `task.md` §17, §18)

- **Contact Management**: a client's own customer database — name, phone, email, city, tags, consent, consent source. Manual add + CSV/Excel import.
- **Campaign Management**: create a campaign (pick a survey + a channel + a contact segment), send it, track delivery/read/click/response per recipient, retry failures.
- **Channels**: SMS, WhatsApp, Email — each behind a provider interface.
- **QR Code system, for real this time**: persisted, labeled (Table 5, Reception Desk, Poster...), multiple formats, tied to a survey.
- **Short links**: for SMS character limits and click tracking across every channel.

---

## 2. Database

- `contacts` — client_id, name, phone (encrypted), email (encrypted), city, consent, consent_source, created_at.
- `contact_tags` / `contact_tag_pivot` — client-scoped tags for segment targeting.
- `campaigns` — client_id, survey_id, name, type (`sms`/`whatsapp`/`email` — **not** qr/short_link, see gap below), status (draft/scheduled/sending/completed/failed), scheduled_at, sent_at, message_template, provider, stats (json cache), created_by.
- `campaign_recipients` — campaign_id, contact_id, channel, status (pending/sent/delivered/read/clicked/responded/failed), sent_at/delivered_at/clicked_at/responded_at, error_message, provider_message_id, short_link_id.
- `short_links` — code, target_url, click_count, last_clicked_at (usable standalone or referenced by a `campaign_recipients` row).
- `qr_codes` — client_id, survey_id, label, format (svg/pdf — see gap below), file_path, short_link_id.

**Additions to existing tables, finally landing**: `responses.contact_id` (nullable FK `contacts`) and `responses.campaign_id` (nullable FK `campaigns`) — both were left off in Phase 4 specifically because these tables didn't exist yet.

**Scope correction vs. the master schema sketch**: `campaigns.type` drops `qr` and `short_link` as campaign types. A QR code isn't sent to a list of recipients and has no delivery/read/click-per-contact tracking the way SMS/WhatsApp/Email do — forcing it through `campaign_recipients` would mean fake rows with no real recipient. QR generation is its own feature (`QrCode` model, directly tied to a survey), and short links are a shared utility both campaigns and QR codes can reference for click tracking, not a campaign type in their own right.

---

## 3. Architecture decisions

- **Provider abstraction, log-by-default** — mirrors Phase 2's `QuestionTypeContract`/registry pattern exactly. `SmsProviderContract` and `WhatsAppProviderContract`, each with `send(CampaignRecipient, string $message): SendResult`, resolved through a `config/campaign_providers.php` map the same way `config/question_types.php` resolves question types. The bound default for both is a `LogProvider` that writes the outgoing message to the log and marks the recipient `sent` — this is what every test and every demo run exercises. Swapping in a real MSG91/Interakt/etc. client later is one class + one config line, same seam as adding a 16th question type.
- **Email is the one channel that's actually real, not simulated**, because Laravel's `Mail` facade already *is* a working provider abstraction — SMTP/SES/Mailgun/Brevo/Postmark are just config, not custom HTTP clients. `EmailProvider` sends through `Mail::raw()`/a Mailable, using whatever `MAIL_MAILER` is configured (`log` by default in this environment, same as everywhere else in the app). No abstraction had to be invented for this one.
- **Consent is enforced at segment-build time, not send time** (master gap #10): building a campaign's recipient list silently excludes any contact with `consent = false`, and the campaign creation screen shows how many were excluded and why — never a silent, unexplained drop.
- **Sending is queued**, not synchronous. `SendCampaignJob` iterates recipients and dispatches one `SendCampaignMessageJob` per recipient onto the existing database queue — a campaign of 500 contacts can't hang an HTTP request, and a provider timeout on recipient #12 can't block recipients #13–500.
- **Short links are a shared utility, not a campaign concern.** `ShortLinkService::createFor(string $targetUrl): ShortLink` is called by both the campaign message composer (SMS body text, WhatsApp buttons) and the QR system. A public `GET /l/{code}` route increments `click_count`, stamps `campaign_recipients.clicked_at` if the short link is tied to one, and redirects to the target survey URL.
- **QR: SVG + PDF, not PNG** — same reasoning as Phase 3, restated because it applies even harder here: the PNG backend needs the `imagick` PHP extension, still not installed. SVG covers on-screen/digital use; PDF (via `barryvdh/laravel-dompdf`, already in the original tech stack and now installed for the first time) wraps the SVG in a printable page for the physical use cases §17 actually lists — table tents, posters, reception-desk cards, business cards, flyers. Revisit PNG only if `imagick` gets installed.
- **CSV/Excel import uses `maatwebsite/excel`** (in the original tech stack, installed now for the first time) — a straightforward import mapping columns to name/phone/email/city/tags, with per-row validation errors surfaced rather than failing the whole import silently.
- **The DLT/Meta compliance blocker is restated here, explicitly, once more**: this phase's code is complete and demonstrable end-to-end via the log provider. Nothing here sends a real message to a real phone number. Before this can go live, the user needs — independent of any code — a TRAI DLT-registered SMS template and entity ID, and a Meta-approved WhatsApp Business template. That work has to start now if real sending matters soon; it does not block finishing this phase's code.

---

## 4. Implementation breakdown

**Models**: `Contact` (belongsTo Client, belongsToMany ContactTag), `ContactTag`, `Campaign` (belongsTo Client, Survey; hasMany CampaignRecipient), `CampaignRecipient` (belongsTo Campaign, Contact), `ShortLink`, `QrCode` (belongsTo Client, Survey).

**Services**:
- `ContactService` — CRUD, `importFromSpreadsheet(Client, UploadedFile)` (maatwebsite/excel, per-row validation), tag assignment.
- `CampaignService` — `create`, `buildRecipients(Campaign, array $tagIds)` (consent-filtered), `send(Campaign)` (dispatches `SendCampaignJob`), `retryFailed(Campaign)`.
- `SmsProviderContract` / `WhatsAppProviderContract` + `LogProvider` implementations, resolved via a small `CampaignProviderRegistry` (same shape as `QuestionTypeRegistry`).
- `EmailProvider` (uses `Mail` facade directly - no contract needed, Laravel's Mailer already is the abstraction).
- `ShortLinkService` — create + resolve + click-tracking.
- `QrCodeService` — generate (SVG + PDF), persist, label.

**Jobs**: `SendCampaignJob` (fans out per-recipient jobs), `SendCampaignMessageJob` (calls the right provider for one recipient, updates its status, handles retry/backoff on failure).

**Controllers** (`App\Http\Controllers\Admin\*`): `ContactController` (nested under a client), `ContactImportController`, `CampaignController` (create/send/retry), `QrCodeController` (generate/download/list for a survey). Plus one public controller: `ShortLinkController@redirect` (no guard, like the survey response routes).

**Routes**: `admin/clients/{client}/contacts*`, `admin/campaigns*` (permission-gated, reusing `manage-surveys`... actually a new `manage-campaigns` permission, since sending real messages to real customers is a materially different responsibility than building surveys — seeded onto `super_admin` and `survyra_admin` alongside the existing permissions), `admin/surveys/{survey}/qr-codes*`, and the public `GET /l/{code}`.

**Migrations**: the 6 new tables above, plus the `add_contact_id_and_campaign_id_to_responses_table` migration finally closing out Phase 4's deferral.

---

## 5. Tests (Pest)

- Contact CRUD and CSV import (valid rows imported, invalid rows reported without failing the whole file).
- Building a campaign's recipient list excludes non-consented contacts and reports the exclusion count.
- Sending a campaign (log provider) marks every recipient `sent` and writes one log line each; a simulated provider failure marks that recipient `failed` without affecting others.
- Retry-failed only re-attempts `failed` recipients, not the whole campaign.
- Short link redirect increments `click_count` and stamps the linked recipient's `clicked_at`.
- QR generation produces valid SVG and PDF output, persists a labeled `qr_codes` row, and is retrievable per survey.
- A response arriving via a campaign's tracked link correctly stamps `contact_id`/`campaign_id`.
- Permission/guard isolation mirroring every prior phase: `send-campaigns` gate on every admin route above; `client` guard redirected away from all of it.

---

## 6. Verification

- `php artisan test` — new Phase 5 suites green alongside the existing 67 tests from Phases 1–4.
- Manual walkthrough: import a small CSV of contacts for Demo Cafe → tag a few as "regulars" → create an SMS campaign targeting that tag for the published "Patient Satisfaction" survey → send it → confirm the log shows one outgoing message per consented contact and `campaign_recipients` shows `sent` → open a QR code for the same survey, download both SVG and PDF → visit a generated short link and confirm the click is tracked and it redirects to the live survey.

---

## 7. Phase 5 status: DONE (as of this build)

Everything above is implemented and verified: `migrate:fresh --seed` runs clean; 85 Pest tests pass (67 from Phases 1–4 + 18 new); a full manual HTTP walkthrough confirmed contact management, CSV import, campaign creation/sending via the log providers, QR generation/download in both formats, and — the actual point of building short links — clicking a campaign's tracked link both stamped `clicked_at` and correctly attributed the resulting survey response's `contact_id`/`campaign_id`/`source` back to that contact and campaign.

Deviations/bugs caught during this build:

- **Reused the existing `send-campaigns` permission instead of adding a redundant `manage-campaigns` one.** Phase 1's `RoleSeeder` had already seeded `send-campaigns` for both `super_admin` and `survyra_admin`, anticipating this phase. The plan draft above still says `manage-campaigns` in a couple of places — the actual code uses `send-campaigns`, and no seeder changes were needed at all.
- **A CSV import bug**: PhpSpreadsheet auto-detects numeric-looking cells (a phone number like `+911111111111`) as an int/float, not a string, so a `'phone' => ['string']` validation rule rejected perfectly valid rows. Fixed by dropping the `string` type constraint from the per-row validation rules (letting numeric-or-string through) and explicitly casting every field to `(string)` in `ContactsImport::collection()` before saving — the validation layer no longer assumes PhpSpreadsheet's type-guessing matches the CSV author's intent.
- **`QrCodeController::download()`'s declared return type didn't match reality** — same class of bug as Phase 4's `SurveyResponseController::show()`. `Storage::disk()->download()` returns `Symfony\Component\HttpFoundation\StreamedResponse`, not `Illuminate\Http\Response`. This one **wasn't caught by the automated tests at all** — the Pest suite checked the generated file existed and had the right content, but never actually called the download route over HTTP. Only the manual browser-level walkthrough caught it (a real 500 on every download click). Fixed the return type and added an HTTP-level test (`the download route actually streams the qr file over http`) specifically to close that gap — a reminder that asserting "the data is correct" isn't the same as asserting "the endpoint that serves it actually works."
- **Removed a dead `QrCodeController::index()` action and its route** before it shipped: the survey builder's QR Codes tab already lists/generates/downloads inline, so a separate listing page pointed at a view that was never built. Caught by the same manual walkthrough, not by tests (guard-isolation tests exercised the route but never got past the middleware layer, so the missing view was never actually reached in that path).
- **Confirmed the deferred Phase 4 integration point actually works**: a short link tied to a `campaign_recipient` now appends `?cr={id}` to its redirect target, and `ResponseService::startOrResume()` reads that to stamp `contact_id`/`campaign_id`/`source` on the newly created response — closing the loop those two columns were added for back in this phase's migrations.
