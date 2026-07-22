<?php

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponse;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Services\SurveyService;
use Database\Seeders\QuestionTypeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(QuestionTypeSeeder::class);
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
});

function publishedSectionWizardSurvey(int $questionCount = 5): Survey
{
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create(['layout' => 'section_wizard']);
    $textbox = QuestionType::query()->where('key', 'textbox')->firstOrFail();

    for ($i = 0; $i < $questionCount; $i++) {
        $template->questions()->create(['question_type_id' => $textbox->id, 'question_text' => "Question {$i}", 'order' => $i]);
    }

    $admin = User::factory()->create();
    $admin->assignRole('survyra_admin');
    $admin->givePermissionTo('manage-surveys');

    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Section Wizard Survey', $admin->id);
    app(SurveyService::class)->publish($survey);

    return $survey->fresh(['questions', 'thankyouRules']);
}

test('a section-wizard survey shows only the first section of questions, three at a time', function () {
    $survey = publishedSectionWizardSurvey(5);

    $page = $this->get("/s/{$survey->slug}");

    $page->assertOk();
    $page->assertSee('Section 1 of 2');
    $page->assertSee('Question 0');
    $page->assertSee('Question 1');
    $page->assertSee('Question 2');
    $page->assertDontSee('Question 3');
    $page->assertDontSee('Submit Survey');
});

test('answering every required question in a section auto-advances to the next section', function () {
    $survey = publishedSectionWizardSurvey(5);
    [$q0, $q1, $q2] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $q0->id, 'answer' => 'a0']);
    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $q1->id, 'answer' => 'a1']);
    $result = $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $q2->id, 'answer' => 'a2']);

    $result->assertOk();
    expect($result->json('html'))->toContain('Section 2 of 2');
    expect($result->json('html'))->toContain('Question 3');
    expect($result->json('html'))->toContain('Question 4');
    expect($result->json('html'))->toContain('Submit Survey');
});

test('submitting a section-wizard survey before the final section is complete returns a 422', function () {
    $survey = publishedSectionWizardSurvey(5);
    [$q0, $q1, $q2] = $survey->questions->all();

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $q0->id, 'answer' => 'a0']);
    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $q1->id, 'answer' => 'a1']);
    $this->postJson("/s/{$survey->slug}/answer", ['response_uuid' => $responseUuid, 'question_id' => $q2->id, 'answer' => 'a2']);

    $result = $this->postJson("/s/{$survey->slug}/submit", ['response_uuid' => $responseUuid]);

    $result->assertStatus(422);
});

test('a section-wizard survey can be completed once every section is answered', function () {
    $survey = publishedSectionWizardSurvey(5);
    $responseUuid = null;

    $this->get("/s/{$survey->slug}");
    $responseUuid = SurveyResponse::query()->where('survey_id', $survey->id)->first()->uuid;

    foreach ($survey->questions as $index => $question) {
        $this->postJson("/s/{$survey->slug}/answer", [
            'response_uuid' => $responseUuid,
            'question_id' => $question->id,
            'answer' => "answer-{$index}",
        ]);
    }

    $result = $this->postJson("/s/{$survey->slug}/submit", ['response_uuid' => $responseUuid]);

    $result->assertOk();
    $this->assertDatabaseHas('responses', ['uuid' => $responseUuid, 'status' => 'completed']);
});
