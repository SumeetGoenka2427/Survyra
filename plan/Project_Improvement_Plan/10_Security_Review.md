# 10 – Security Review

> Comprehensive security audit of the current implementation with recommendations.

---

## Authentication

### Current State
- Two separate guards: `web` (admin `User`) + `client` (`ClientUser`) — correctly isolated.
- Password reset implemented for both guards.
- `is_active` flag on `User` model — but is it checked on login?
- `ClientUser` has a separate password reset tokens table.

### Issues
1. **Email verification disabled** — `MustVerifyEmail` is commented out in `User` model. New admin accounts are not verified.
2. **No 2FA** — No two-factor authentication for either guard.
3. **No account lockout** — No brute-force protection beyond Laravel's default throttle middleware.
4. **`is_active` not enforced on `ClientUser`** — Need to verify the `client` guard checks this.

### Recommendations
```php
// 1. Enable email verification
class User extends Authenticatable implements MustVerifyEmail { ... }

// 2. Add login throttle (already in Breeze, verify it's applied)
Route::middleware(['auth:web', 'verified'])->group(function () { ... });

// 3. Add 2FA (pragmarx/google2fa-laravel)
// 4. Add account lockout after 5 failed attempts
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->input('email').$request->ip());
});
```

---

## Authorization

### Current State
- Spatie `laravel-permission` with roles and permissions.
- Policies: `CampaignPolicy`, `ClientPolicy`, `ContactPolicy`, `SurveyPolicy`, `SurveyTemplatePolicy`, `SurveyThemePolicy`.
- Portal routes protected by `auth:client` middleware.

### Issues
1. **No policy for `Report`** — Reports can potentially be accessed across clients.
2. **No policy for `Response`** — Response detail pages need client scoping verification.
3. **`ClientUser` can only access their own client's data** — Verify this is enforced in all portal controllers.
4. **Admin can access any client's data** — Intentional, but should be logged.

### Recommendations
```php
// Add ReportPolicy and ResponsePolicy
// Verify all portal controllers scope queries to auth()->user()->client_id
// Add activity logging on sensitive admin actions (client data access)
```

---

## Survey Privacy

### Current State
- Surveys are public by default once published (accessible via `/s/{slug}`).
- No password protection.
- No anonymous mode toggle.
- No domain restriction.

### Issues
1. **No access control on public surveys** — Anyone with the slug can respond.
2. **No anonymous mode** — IP, device, browser always collected.
3. **No response deduplication** — Same person can respond multiple times (cookie-based only, easily bypassed).

### Recommendations
```php
// 1. Password protection
if ($survey->password && !Hash::check($request->input('password'), $survey->password)) {
    return redirect()->back()->withErrors(['password' => 'Incorrect password.']);
}

// 2. Anonymous mode
if (!$survey->is_anonymous) {
    // collect IP, device, browser
}

// 3. One-response-per-IP option
if ($survey->settings['one_response_per_ip'] ?? false) {
    $existing = Response::where('survey_id', $survey->id)->where('ip', $request->ip())->exists();
    if ($existing) abort(403, 'You have already responded to this survey.');
}
```

---

## Data Encryption

### Current State
- Contact `phone` and `email` encrypted using Laravel's `encrypted` cast.
- Client `support_number` and `whatsapp_number` encrypted.
- No encryption on response answers (text answers stored in plain JSON).

### Issues
1. **Response answers not encrypted** — Text answers may contain PII (names, addresses, medical info).
2. **Survey settings not encrypted** — May contain sensitive configuration.

### Recommendations
```php
// For sensitive question types (email, phone, textbox in healthcare surveys):
// Add an 'encrypt_answers' flag to survey settings
// Apply encryption in ResponseService::saveAnswer()

// Minimum: encrypt the 'answer' column in response_answers for sensitive surveys
```

---

## Rate Limiting

### Current State
- Survey routes: `throttle:60,1` (60 requests per minute per IP).
- No rate limiting on admin/portal API endpoints.

### Issues
1. **60 req/min may be too high** for survey submission (allows automated submissions).
2. **No rate limiting on analytics export** — Could be used to scrape all response data.
3. **No rate limiting on contact import** — Could be used to flood the system.

### Recommendations
```php
// Survey submission: stricter limit
Route::post('{slug}/submit', ...)->middleware('throttle:5,1'); // 5 per minute

// Analytics export
Route::get('export/{format}', ...)->middleware('throttle:10,60'); // 10 per hour

// API endpoints
Route::middleware('throttle:1000,60'); // 1000 per hour per API key
```

---

## reCAPTCHA / Bot Protection

### Current State
- No CAPTCHA on survey submission.
- Rate limiting is the only protection.

### Recommendations
```php
// Add Google reCAPTCHA v3 (invisible, no user friction)
// Verify token server-side on survey submit

// config/services.php
'recaptcha' => [
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'threshold' => 0.5, // score below this = bot
],

// In SurveyResponseController::submit()
$score = Http::post('https://www.google.com/recaptcha/api/siteverify', [
    'secret' => config('services.recaptcha.secret_key'),
    'response' => $request->input('g-recaptcha-response'),
])->json('score');

if ($score < config('services.recaptcha.threshold')) {
    abort(422, 'Bot detection triggered.');
}
```

---

## GDPR Compliance

### Current State
- Contact `consent` flag exists with `consent_source`.
- Campaign service excludes non-consented contacts.
- No GDPR consent checkbox on surveys.
- No data export for individual respondents.
- No account/data deletion workflow.

### Issues
1. **No GDPR consent on survey** — Surveys collecting PII (email, phone) must show a consent checkbox.
2. **No "right to be forgotten"** — No way for a respondent to request data deletion.
3. **No data export for respondents** — GDPR requires data portability.
4. **No data retention policy** — Old responses should be auto-deleted after X days.
5. **No privacy policy link** — Survey footer should link to privacy policy.

### Recommendations
```php
// 1. GDPR consent question type (or survey-level toggle)
// 2. Data retention: scheduled command to delete responses older than retention_days
// 3. Privacy policy URL in survey settings
// 4. Admin: "Delete all data for respondent" by email/IP

// App\Console\Commands\PurgeExpiredResponses
public function handle(): void
{
    $retentionDays = config('app.response_retention_days', 365);
    Response::where('completed_at', '<', now()->subDays($retentionDays))->delete();
}
```

---

## Audit Logs

### Current State
- `spatie/laravel-activitylog` installed and migrated.
- No UI to view logs.
- Unclear which models/actions are being logged.

### Recommendations
```php
// 1. Add LogsActivity trait to key models
class Survey extends Model {
    use LogsActivity;
    protected static $logAttributes = ['title', 'status', 'settings'];
    protected static $logOnlyDirty = true;
}

// 2. Add admin UI: /admin/audit-log
// 3. Log: client creation, survey publish/archive, campaign send, admin login

// 4. Log admin access to client data
activity('admin_access')
    ->causedBy(auth()->user())
    ->withProperties(['client_id' => $client->id])
    ->log('Accessed client data');
```

---

## Backups

### Current State
- No automated backup configuration found.

### Recommendations
```bash
# Use spatie/laravel-backup
composer require spatie/laravel-backup

# config/backup.php - backup DB + storage to S3 daily
# Schedule: $schedule->command('backup:run')->daily()->at('02:00');
# Alert on failure: $schedule->command('backup:monitor')->daily()->at('03:00');
```

---

## Input Validation & XSS

### Current State
- Laravel form requests used for validation.
- Blade templates auto-escape output (`{{ }}`).

### Issues
1. **`custom_css` in SurveyTheme** — Custom CSS is rendered directly. Could inject malicious CSS.
2. **Survey question text** — Rendered in survey views; verify it's escaped.
3. **Campaign message template** — Contains `{survey_link}` placeholder; verify no XSS.

### Recommendations
```php
// Sanitize custom_css: strip <script> tags and javascript: URLs
// Use HTMLPurifier for any rich text fields
// Validate custom_css against a CSS allowlist

// In SurveyTheme validation:
'custom_css' => ['nullable', 'string', 'max:10000', new SafeCssRule()],
```

---

## Security Headers

### Recommendations
Add security headers via middleware:
```php
// App\Http\Middleware\SecurityHeaders
$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-XSS-Protection', '1; mode=block');
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
// CSP header (careful with inline scripts)
```

---

## Summary: Security Priority Matrix

| Issue | Severity | Effort | Fix |
|---|---|---|---|
| No reCAPTCHA on surveys | High | Easy | Add reCAPTCHA v3 |
| No GDPR consent checkbox | High | Easy | Add consent question type |
| Email verification disabled | High | Easy | Uncomment MustVerifyEmail |
| No 2FA | Medium | Medium | Add TOTP |
| Response answers not encrypted | Medium | Medium | Encrypt sensitive answers |
| No audit log UI | Medium | Easy | Add admin page |
| No data retention policy | Medium | Easy | Add scheduled command |
| Custom CSS XSS risk | Medium | Easy | Add CSS sanitization |
| No account lockout | Medium | Easy | Already in Breeze throttle |
| No backups | High | Easy | Add spatie/laravel-backup |
