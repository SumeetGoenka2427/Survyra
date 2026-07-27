<?php

use App\Models\Client;
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

function thankyouRuleAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

test('the show_instagram checkbox appears on the thank-you tab and can be toggled on through the admin UI', function () {
    $admin = thankyouRuleAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Instagram Survey', $admin->id);

    $editPage = $this->actingAs($admin)->get(route('admin.surveys.edit', $survey));
    $editPage->assertOk();
    $editPage->assertSee('Show Instagram link');

    $response = $this->actingAs($admin)->put(route('admin.surveys.thankyou-rules.update', [$survey, 'positive']), [
        'thank_you_message' => 'Thanks so much!',
        'show_instagram' => '1',
        'show_facebook' => '1',
    ]);

    $response->assertRedirect();

    $rule = $survey->fresh()->thankyouRules->firstWhere('sentiment', 'positive');
    expect($rule->show_instagram)->toBeTrue();
    expect($rule->show_facebook)->toBeTrue();
});

test('unchecking show_instagram turns it back off', function () {
    $admin = thankyouRuleAdmin();
    $client = Client::factory()->create();
    $template = SurveyTemplate::factory()->create();
    $survey = app(SurveyService::class)->createFromTemplate($client, $template, 'Instagram Survey', $admin->id);

    $this->actingAs($admin)->put(route('admin.surveys.thankyou-rules.update', [$survey, 'positive']), [
        'thank_you_message' => 'Thanks!',
        'show_instagram' => '1',
    ]);

    $this->actingAs($admin)->put(route('admin.surveys.thankyou-rules.update', [$survey, 'positive']), [
        'thank_you_message' => 'Thanks!',
    ]);

    $rule = $survey->fresh()->thankyouRules->firstWhere('sentiment', 'positive');
    expect($rule->show_instagram)->toBeFalse();
});
