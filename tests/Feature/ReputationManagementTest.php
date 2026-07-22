<?php

use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Contact;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponse;
use App\Models\ReviewClick;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Notifications\CampaignSendCompleted;
use App\Notifications\NegativeFeedbackReceived;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
    Permission::findOrCreate('send-campaigns', 'web');
});

function reputationAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo(['manage-surveys', 'send-campaigns']);

    return $user;
}

function publishedNpsSurveyForReputation(Client $client, User $admin): \App\Models\Survey
{
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'How likely to recommend?', 'order' => 0]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Reputation Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey->fresh(['questions', 'thankyouRules']);
}

test('completing a survey with negative feedback notifies every client user but not another clients users', function () {
    Notification::fake();

    $admin = reputationAdmin();
    $clientA = Client::factory()->create(['google_review_url' => 'https://g.page/demo-cafe/review']);
    $clientB = Client::factory()->create();
    $survey = publishedNpsSurveyForReputation($clientA, $admin);
    $question = $survey->questions->first();

    $ownerA = ClientUser::factory()->create(['client_id' => $clientA->id]);
    $staffB = ClientUser::factory()->create(['client_id' => $clientB->id]);

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => 3,
    ]);

    Notification::assertSentTo($ownerA, NegativeFeedbackReceived::class);
    Notification::assertNotSentTo($staffB, NegativeFeedbackReceived::class);

    $this->assertDatabaseHas('responses', ['uuid' => $responseUuid, 'sentiment' => 'negative']);
});

test('completing a survey with positive feedback never sends a negative feedback notification', function () {
    Notification::fake();

    $admin = reputationAdmin();
    $client = Client::factory()->create();
    $survey = publishedNpsSurveyForReputation($client, $admin);
    $question = $survey->questions->first();
    $owner = ClientUser::factory()->create(['client_id' => $client->id]);

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => 10,
    ]);

    Notification::assertNotSentTo($owner, NegativeFeedbackReceived::class);
});

test('a campaign reaching completion notifies its creator exactly once', function () {
    Notification::fake();

    $admin = reputationAdmin();
    $client = Client::factory()->create();
    $survey = publishedNpsSurveyForReputation($client, $admin);
    Contact::factory()->for($client)->create(['consent' => true, 'email' => 'a@example.com']);

    $this->actingAs($admin)->post('/admin/campaigns', [
        'client_id' => $client->id,
        'survey_id' => $survey->id,
        'name' => 'Notify Campaign',
        'type' => 'email',
        'message_template' => 'Hi {name}: {link}',
    ]);
    $campaign = Campaign::query()->where('name', 'Notify Campaign')->firstOrFail();

    $this->actingAs($admin)->post("/admin/campaigns/{$campaign->id}/send");

    Notification::assertSentToTimes($admin, CampaignSendCompleted::class, 1);
});

test('hitting the review click redirect logs a click and redirects to the real target', function () {
    $admin = reputationAdmin();
    $client = Client::factory()->create([
        'google_review_url' => 'https://g.page/demo-cafe/review',
        'whatsapp_number' => '+911234567890',
        'support_number' => '+911234567890',
    ]);
    $survey = publishedNpsSurveyForReputation($client, $admin);

    $this->get("/s/{$survey->slug}");
    $response = SurveyResponse::query()->where('survey_id', $survey->id)->first();

    $result = $this->get("/r/{$response->uuid}/google_review");

    $result->assertRedirect('https://g.page/demo-cafe/review');
    $this->assertDatabaseHas('review_clicks', [
        'response_id' => $response->id,
        'client_id' => $client->id,
        'channel' => 'google_review',
    ]);
});

test('an unknown channel or unknown response on the review click route returns 404 not a server error', function () {
    $admin = reputationAdmin();
    $client = Client::factory()->create(['google_review_url' => 'https://g.page/demo-cafe/review']);
    $survey = publishedNpsSurveyForReputation($client, $admin);

    $this->get("/s/{$survey->slug}");
    $response = SurveyResponse::query()->where('survey_id', $survey->id)->first();

    $this->get("/r/{$response->uuid}/not-a-real-channel")->assertNotFound();
    $this->get('/r/00000000-0000-0000-0000-000000000000/google_review')->assertNotFound();

    expect(ReviewClick::query()->count())->toBe(0);
});

test('the notification bell mark-read endpoint only affects the authenticated users own notifications', function () {
    $admin = reputationAdmin();
    $client = Client::factory()->create();
    $survey = publishedNpsSurveyForReputation($client, $admin);
    $question = $survey->questions->first();

    $owner = ClientUser::factory()->create(['client_id' => $client->id]);
    $otherOwner = ClientUser::factory()->create();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;
    $this->postJson("/s/{$survey->slug}/answer", [
        'response_uuid' => $responseUuid,
        'question_id' => $question->id,
        'answer' => 2,
    ]);

    $notification = $owner->fresh()->notifications()->firstOrFail();

    // Another client user can't mark someone else's notification as read.
    $this->actingAs($otherOwner, 'client')
        ->postJson("/portal/notifications/{$notification->id}/read")
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();

    $result = $this->actingAs($owner, 'client')->postJson("/portal/notifications/{$notification->id}/read");

    $result->assertOk();
    expect($result->json('unreadCount'))->toBe(0);
    expect($notification->fresh()->read_at)->not->toBeNull();
});
