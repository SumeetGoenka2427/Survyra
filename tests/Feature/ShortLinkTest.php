<?php

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Client;
use App\Models\Contact;
use App\Models\QuestionType;
use App\Models\Response;
use App\Models\ShortLink;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\ShortLinkService;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
});

test('visiting a standalone short link redirects and increments the click count', function () {
    $shortLink = app(ShortLinkService::class)->createFor('https://example.com/target');

    $response = $this->get("/l/{$shortLink->code}");

    $response->assertRedirect('https://example.com/target');
    expect($shortLink->fresh()->click_count)->toBe(1);
});

test('an unknown short code returns a 404', function () {
    $this->get('/l/doesnotexist')->assertNotFound();
});

test('clicking a campaign short link stamps the recipient and attributes the resulting response', function () {
    $admin = User::factory()->create();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'Score?', 'order' => 0]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Tracked Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    $contact = Contact::factory()->for($client)->create();
    $campaign = Campaign::query()->create([
        'client_id' => $client->id,
        'survey_id' => $survey->id,
        'name' => 'Tracked Campaign',
        'type' => 'sms',
        'status' => 'draft',
        'message_template' => 'Hi {name}: {link}',
    ]);
    $recipient = CampaignRecipient::query()->create([
        'campaign_id' => $campaign->id,
        'contact_id' => $contact->id,
        'channel' => 'sms',
        'status' => 'sent',
    ]);
    $shortLink = ShortLink::query()->create([
        'code' => 'trackd',
        'target_url' => url("/s/{$survey->slug}"),
    ]);
    $recipient->update(['short_link_id' => $shortLink->id]);

    $redirect = $this->get('/l/trackd');
    $redirect->assertRedirect();
    expect($redirect->headers->get('Location'))->toContain('cr='.$recipient->id);

    expect($recipient->fresh()->clicked_at)->not->toBeNull();

    $this->get($redirect->headers->get('Location'));

    $createdResponse = Response::query()->where('survey_id', $survey->id)->firstOrFail();
    expect($createdResponse->contact_id)->toBe($contact->id);
    expect($createdResponse->campaign_id)->toBe($campaign->id);
    expect($createdResponse->source)->toBe('sms');
});
