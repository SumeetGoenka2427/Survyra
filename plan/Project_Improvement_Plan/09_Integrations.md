# 09 – Integrations

> Implementation plan for all missing integrations.

---

## Foundation: REST API & Webhooks

All third-party integrations depend on these two foundations. Build them first.

---

## 1. REST API (Public)

**Why**: Enables Zapier, Make, custom integrations, and developer access.

**Authentication**: API key in `Authorization: Bearer {key}` header.

**Endpoints**:
```
GET    /api/v1/surveys              List surveys
GET    /api/v1/surveys/{id}         Get survey
GET    /api/v1/surveys/{id}/responses  List responses
GET    /api/v1/responses/{id}       Get response with answers
POST   /api/v1/surveys/{id}/responses  Create response (for external submission)
GET    /api/v1/contacts             List contacts
POST   /api/v1/contacts             Create contact
```

**Implementation**:
```php
// routes/api.php
Route::prefix('v1')->middleware('auth:api_key')->group(function () {
    Route::apiResource('surveys', Api\SurveyController::class)->only(['index', 'show']);
    Route::get('surveys/{survey}/responses', [Api\ResponseController::class, 'index']);
    Route::apiResource('responses', Api\ResponseController::class)->only(['show']);
    Route::apiResource('contacts', Api\ContactController::class)->only(['index', 'store']);
});

// ApiKeyMiddleware
public function handle(Request $request, Closure $next): Response
{
    $key = $request->bearerToken();
    $apiKey = ApiKey::where('key_hash', hash('sha256', $key))->where('is_active', true)->first();
    
    if (!$apiKey || ($apiKey->expires_at && $apiKey->expires_at->isPast())) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    $apiKey->update(['last_used_at' => now()]);
    app()->instance('current_client', $apiKey->client);
    
    return $next($request);
}
```

**Rate limiting**: 1000 requests/hour per API key.

---

## 2. Webhooks

**Why**: Real-time push notifications to external systems on survey events.

**Events**:
- `response.started`
- `response.completed`
- `campaign.completed`

**Implementation**:
```php
// App\Jobs\DeliverWebhookJob
public function handle(): void
{
    $payload = [
        'event' => $this->event,
        'timestamp' => now()->toIso8601String(),
        'data' => $this->data,
    ];
    
    $signature = hash_hmac('sha256', json_encode($payload), $this->webhook->secret ?? '');
    
    Http::withHeaders([
        'X-Survyra-Signature' => $signature,
        'Content-Type' => 'application/json',
    ])->timeout(10)->post($this->webhook->url, $payload);
}

// Fire in ResponseService::submit()
$webhooks = Webhook::where('client_id', $response->client_id)
    ->where('is_active', true)
    ->whereJsonContains('events', 'response.completed')
    ->get();

foreach ($webhooks as $webhook) {
    DeliverWebhookJob::dispatch($webhook, 'response.completed', $response->toArray());
}
```

**Retry**: 3 retries with exponential backoff. After 10 consecutive failures, auto-disable webhook.

---

## 3. Zapier Integration

**Why**: Connects Survyra to 5,000+ apps without code.

**Implementation**: Zapier uses webhooks + REST API. No Zapier-specific code needed.

**Steps**:
1. Build REST API (above).
2. Build Webhooks (above).
3. Create a Zapier app (or use "Webhooks by Zapier" trigger pointing to our webhook URL).
4. Document the trigger: "New Response Completed" → sends response data to Zapier.

**Zapier App Triggers**:
- New Response Completed
- New Negative Response

**Zapier App Actions**:
- Create Contact
- Get Survey Responses

---

## 4. Google Sheets Integration

**Why**: Most SMBs use Google Sheets for data analysis. Auto-sync eliminates manual CSV exports.

**Implementation**:
```php
// App\Services\Integrations\GoogleSheetsService
public function syncResponse(Response $response, string $spreadsheetId, string $sheetName): void
{
    $client = new Google_Client();
    $client->setAccessToken($this->getAccessToken($response->client));
    
    $sheets = new Google_Service_Sheets($client);
    
    $row = [
        $response->uuid,
        $response->started_at->toDateTimeString(),
        $response->completed_at?->toDateTimeString(),
        $response->sentiment,
        $response->score,
        // ... answer columns
    ];
    
    $sheets->spreadsheets_values->append(
        $spreadsheetId,
        $sheetName,
        new Google_Service_Sheets_ValueRange(['values' => [$row]]),
        ['valueInputOption' => 'RAW']
    );
}
```

**OAuth flow**: Client connects Google account in portal settings. Store OAuth tokens encrypted.

**Trigger**: Fire `SyncToGoogleSheetsJob` from webhook `response.completed` handler.

---

## 5. Slack Notifications

**Why**: Teams want instant Slack alerts on new responses, especially negative ones.

**Implementation**:
```php
// Add slack_webhook_url to client settings or a new integrations table
// App\Notifications\Channels\SlackChannel

public function send(mixed $notifiable, Notification $notification): void
{
    $message = $notification->toSlack($notifiable);
    
    Http::post($notifiable->slack_webhook_url, [
        'text' => $message->content,
        'attachments' => $message->attachments,
    ]);
}

// Usage in NegativeFeedbackReceived notification
public function toSlack($notifiable): SlackMessage
{
    return (new SlackMessage)
        ->content("⚠️ Negative feedback received on *{$this->response->survey->title}*")
        ->attachment(fn($a) => $a
            ->title('Response Details')
            ->fields([
                'Score' => $this->response->score,
                'Sentiment' => $this->response->sentiment,
                'Time' => $this->response->completed_at->diffForHumans(),
            ])
        );
}
```

**Setup**: Client pastes Slack Incoming Webhook URL in portal settings.

---

## 6. Microsoft Teams Notifications

**Why**: Enterprise clients use Teams instead of Slack.

**Implementation**: Same as Slack — Teams supports Incoming Webhooks with the same JSON format.

---

## 7. WhatsApp Business API

**Why**: Already have WhatsApp campaign type. Need a proper provider implementation.

**Implementation**:
```php
// App\Services\Campaigns\WhatsAppProvider (implements MessageProviderContract)
public function send(CampaignRecipient $recipient, string $message): bool
{
    // Meta Cloud API
    $response = Http::withToken(config('services.whatsapp.token'))
        ->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $recipient->contact->phone,
            'type' => 'text',
            'text' => ['body' => $message],
        ]);
    
    return $response->successful();
}
```

---

## 8. Mailchimp / Brevo Integration

**Why**: Sync survey respondents back to email marketing lists.

**Implementation**:
```php
// On response completion, if contact email exists, add/update in Mailchimp
// App\Jobs\SyncContactToMailchimpJob

public function handle(): void
{
    $mailchimp = new MailchimpMarketing\ApiClient();
    $mailchimp->setConfig(['apiKey' => config('services.mailchimp.key')]);
    
    $mailchimp->lists->addListMember($listId, [
        'email_address' => $this->contact->email,
        'status' => 'subscribed',
        'merge_fields' => [
            'FNAME' => $this->contact->name,
            'NPS_SCORE' => $this->response->score,
            'SENTIMENT' => $this->response->sentiment,
        ],
    ]);
}
```

---

## 9. Google Analytics / Meta Pixel

**Why**: Clients want to track survey page views and completion events in their analytics.

**Implementation**:
```php
// In survey show.blade.php, if settings have tracking IDs:
@if($survey->ga_tracking_id)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $survey->ga_tracking_id }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ $survey->ga_tracking_id }}');
</script>
@endif

@if($survey->meta_pixel_id)
<script>
  !function(f,b,e,v,n,t,s){...}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '{{ $survey->meta_pixel_id }}');
  fbq('track', 'PageView');
</script>
@endif
```

On survey completion, fire a `SurveyCompleted` event to GA/Meta.

---

## 10. Stripe Billing

**Why**: The subscription plan model exists but there is no payment collection.

**Implementation**:
```php
// composer require stripe/stripe-php laravel/cashier

// App\Http\Controllers\Portal\BillingController
public function checkout(Request $request): RedirectResponse
{
    $client = $request->user(); // ClientUser
    $plan = SubscriptionPlan::findOrFail($request->plan_id);
    
    $session = Stripe\Checkout\Session::create([
        'customer_email' => $client->email,
        'line_items' => [['price' => $plan->stripe_price_id, 'quantity' => 1]],
        'mode' => 'subscription',
        'success_url' => route('portal.billing.success'),
        'cancel_url' => route('portal.billing.cancel'),
        'metadata' => ['client_id' => $client->client_id],
    ]);
    
    return redirect($session->url);
}

// Stripe webhook handler
public function handleWebhook(Request $request): Response
{
    $event = Stripe\Webhook::constructEvent(
        $request->getContent(),
        $request->header('Stripe-Signature'),
        config('services.stripe.webhook_secret')
    );
    
    match ($event->type) {
        'customer.subscription.created' => $this->handleSubscriptionCreated($event->data->object),
        'customer.subscription.deleted' => $this->handleSubscriptionCanceled($event->data->object),
        'invoice.payment_failed' => $this->handlePaymentFailed($event->data->object),
        default => null,
    };
    
    return response('OK');
}
```

---

## Integration Settings UI

Add an "Integrations" page in the portal with:
- API Keys management (generate, revoke, copy)
- Webhooks management (add URL, select events, test)
- Connected apps (Google Sheets, Slack, Teams, Mailchimp)
- Tracking pixels (GA, Meta Pixel)
