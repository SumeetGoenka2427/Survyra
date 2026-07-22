<?php

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\QrCode as QrCodeModel;
use App\Models\QuestionType;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
});

function makeQrAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

function publishedSurveyForQr(Client $client, User $admin): \App\Models\Survey
{
    $template = SurveyTemplate::factory()->create();
    $nps = QuestionType::query()->where('key', 'nps')->firstOrFail();
    $template->questions()->create(['question_type_id' => $nps->id, 'question_text' => 'Score?', 'order' => 0]);

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'QR Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey;
}

test('generating an svg qr code persists a labeled row and a real file', function () {
    $admin = makeQrAdmin();
    $client = Client::factory()->create();
    $survey = publishedSurveyForQr($client, $admin);

    $this->actingAs($admin)->post("/admin/surveys/{$survey->id}/qr-codes", [
        'label' => 'Reception Desk',
        'format' => 'svg',
    ])->assertRedirect();

    $qrCode = QrCodeModel::query()->where('survey_id', $survey->id)->firstOrFail();
    expect($qrCode->label)->toBe('Reception Desk');
    expect($qrCode->format)->toBe('svg');
    Storage::disk('public')->assertExists($qrCode->file_path);
    expect(Storage::disk('public')->get($qrCode->file_path))->toContain('<svg');
});

test('generating a pdf qr code persists a real pdf file', function () {
    $admin = makeQrAdmin();
    $client = Client::factory()->create();
    $survey = publishedSurveyForQr($client, $admin);

    $this->actingAs($admin)->post("/admin/surveys/{$survey->id}/qr-codes", [
        'label' => 'Table 5',
        'format' => 'pdf',
    ])->assertRedirect();

    $qrCode = QrCodeModel::query()->where('label', 'Table 5')->firstOrFail();
    Storage::disk('public')->assertExists($qrCode->file_path);
    expect(Storage::disk('public')->get($qrCode->file_path))->toStartWith('%PDF');
});

test('the download route actually streams the qr file over http', function () {
    $admin = makeQrAdmin();
    $client = Client::factory()->create();
    $survey = publishedSurveyForQr($client, $admin);

    $this->actingAs($admin)->post("/admin/surveys/{$survey->id}/qr-codes", [
        'label' => 'Reception Desk',
        'format' => 'svg',
    ]);
    $qrCode = QrCodeModel::query()->where('survey_id', $survey->id)->firstOrFail();

    $this->actingAs($admin)
        ->get("/admin/surveys/{$survey->id}/qr-codes/{$qrCode->id}/download")
        ->assertOk()
        ->assertHeader('content-disposition');
});

test('qr codes can only be generated for published surveys', function () {
    $admin = makeQrAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $draftSurvey = app(SurveyService::class)->createFromTemplate($client, $template, 'Draft Survey', $admin->id);

    $this->actingAs($admin)->post("/admin/surveys/{$draftSurvey->id}/qr-codes", [
        'label' => 'Poster',
        'format' => 'svg',
    ])->assertStatus(422);
});

test('a client portal user is redirected away from qr code routes', function () {
    $clientUser = ClientUser::factory()->create();
    $admin = makeQrAdmin();
    $survey = publishedSurveyForQr(Client::factory()->create(), $admin);

    $this->actingAs($clientUser, 'client')
        ->post("/admin/surveys/{$survey->id}/qr-codes", ['label' => 'Poster', 'format' => 'svg'])
        ->assertRedirect(route('admin.login'));
});
