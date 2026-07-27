<?php

use App\Models\Client;
use App\Models\SurveyLogicRule;
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

function logicRuleAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

function surveyWithTwoQuestions(): \App\Models\Survey
{
    $admin = logicRuleAdmin();
    $client = Client::factory()->create();
    $survey = app(SurveyService::class)->createBlank($client, 'Logic Survey', 'multi_step', $admin->id);
    app(SurveyService::class)->addQuestionsFromDraft($survey, [
        ['type' => 'radio', 'text' => 'Are you happy?', 'options' => ['Yes', 'No']],
        ['type' => 'textarea', 'text' => 'Why not?'],
    ]);

    return $survey->fresh('questions');
}

test('a logic rule can be created through the admin UI', function () {
    $admin = logicRuleAdmin();
    $survey = surveyWithTwoQuestions();
    [$q1, $q2] = $survey->questions;

    $response = $this->actingAs($admin)->post(route('admin.surveys.logic-rules.store', $survey), [
        'conditions' => [['question_id' => $q1->id, 'operator' => 'equals', 'value' => 'No']],
        'condition_operator' => 'AND',
        'action' => 'show',
        'target_question_id' => $q2->id,
    ]);

    $response->assertRedirect();
    expect(SurveyLogicRule::where('survey_id', $survey->id)->count())->toBe(1);
});

test('a logic rule can be edited through the admin UI', function () {
    $admin = logicRuleAdmin();
    $survey = surveyWithTwoQuestions();
    [$q1, $q2] = $survey->questions;

    $rule = $survey->logicRules()->create([
        'source_question_id' => $q1->id,
        'conditions' => [['question_id' => $q1->id, 'operator' => 'equals', 'value' => 'No']],
        'condition_operator' => 'AND',
        'action' => 'show',
        'target_question_id' => $q2->id,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.surveys.logic-rules.update', [$survey, $rule]), [
        'conditions' => [['question_id' => $q1->id, 'operator' => 'equals', 'value' => 'Yes']],
        'condition_operator' => 'AND',
        'action' => 'hide',
        'target_question_id' => $q2->id,
    ]);

    $response->assertRedirect();
    $rule->refresh();
    expect($rule->action)->toBe('hide');
    expect($rule->conditions[0]['value'])->toBe('Yes');
});

test('the logic tab renders an edit form pre-filled with the rule current values', function () {
    $admin = logicRuleAdmin();
    $survey = surveyWithTwoQuestions();
    [$q1, $q2] = $survey->questions;

    $survey->logicRules()->create([
        'source_question_id' => $q1->id,
        'conditions' => [['question_id' => $q1->id, 'operator' => 'equals', 'value' => 'No']],
        'condition_operator' => 'AND',
        'action' => 'show',
        'target_question_id' => $q2->id,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.surveys.edit', $survey));

    $response->assertOk();
    $response->assertSee(route('admin.surveys.logic-rules.update', [$survey, $survey->logicRules->first()]), false);
});

test('a logic rule belonging to a different survey cannot be updated', function () {
    $admin = logicRuleAdmin();
    $survey = surveyWithTwoQuestions();
    $otherSurvey = surveyWithTwoQuestions();
    [$q1, $q2] = $otherSurvey->questions;

    $rule = $otherSurvey->logicRules()->create([
        'source_question_id' => $q1->id,
        'conditions' => [['question_id' => $q1->id, 'operator' => 'equals', 'value' => 'No']],
        'condition_operator' => 'AND',
        'action' => 'show',
        'target_question_id' => $q2->id,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.surveys.logic-rules.update', [$survey, $rule]), [
        'conditions' => [['question_id' => $q1->id, 'operator' => 'equals', 'value' => 'Yes']],
        'condition_operator' => 'AND',
        'action' => 'hide',
        'target_question_id' => $q2->id,
    ]);

    $response->assertSessionHasErrors('rule');
    expect($rule->fresh()->action)->toBe('show');
});

test('a logic rule can be deleted through the admin UI', function () {
    $admin = logicRuleAdmin();
    $survey = surveyWithTwoQuestions();
    [$q1, $q2] = $survey->questions;

    $rule = $survey->logicRules()->create([
        'source_question_id' => $q1->id,
        'conditions' => [['question_id' => $q1->id, 'operator' => 'equals', 'value' => 'No']],
        'condition_operator' => 'AND',
        'action' => 'show',
        'target_question_id' => $q2->id,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.surveys.logic-rules.destroy', [$survey, $rule]));

    $response->assertRedirect();
    expect(SurveyLogicRule::find($rule->id))->toBeNull();
});
