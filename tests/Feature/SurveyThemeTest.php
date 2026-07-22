<?php

use App\Models\ClientUser;
use App\Models\SurveyTheme;
use App\Models\User;
use Database\Seeders\SurveyThemeSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('manage-surveys', 'web');
});

function makeThemeAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('manage-surveys');

    return $user;
}

test('seeding creates the 7 system themes', function () {
    $this->seed(SurveyThemeSeeder::class);

    expect(SurveyTheme::query()->where('is_system', true)->count())->toBe(7);
});

test('a survyra admin can create update and delete a custom theme', function () {
    $admin = makeThemeAdmin();

    $this->actingAs($admin)->post('/admin/themes', [
        'name' => 'Custom Theme',
        'primary_color' => '#123456',
        'secondary_color' => '#654321',
        'background' => '#ffffff',
        'button_style' => 'rounded',
        'font' => 'Inter',
        'progress_bar_style' => 'bar',
        'border_radius' => 8,
    ])->assertRedirect(route('admin.themes.index'));

    $theme = SurveyTheme::query()->where('name', 'Custom Theme')->firstOrFail();
    expect($theme->is_system)->toBeFalse();

    $this->actingAs($admin)->put("/admin/themes/{$theme->id}", [
        'name' => 'Custom Theme Updated',
        'primary_color' => '#000000',
        'secondary_color' => '#654321',
        'background' => '#ffffff',
        'button_style' => 'pill',
        'font' => 'Inter',
        'progress_bar_style' => 'dots',
        'border_radius' => 12,
    ])->assertRedirect(route('admin.themes.index'));

    expect($theme->fresh()->name)->toBe('Custom Theme Updated');

    $this->actingAs($admin)->delete("/admin/themes/{$theme->id}")->assertRedirect(route('admin.themes.index'));
    $this->assertDatabaseMissing('survey_themes', ['id' => $theme->id]);
});

test('the themes ajax data endpoint filters by search', function () {
    $admin = makeThemeAdmin();
    $this->seed(SurveyThemeSeeder::class);

    $result = $this->actingAs($admin)->getJson('/admin/themes/data?search=Dark');

    $result->assertOk();
    expect($result->json('html'))->toContain('Dark');
    expect($result->json('html'))->not->toContain('Minimal');
});

test('deleting a theme through ajax returns the refreshed fragment', function () {
    $admin = makeThemeAdmin();
    $theme = SurveyTheme::query()->create([
        'name' => 'Temp Theme',
        'primary_color' => '#123456',
        'secondary_color' => '#654321',
        'background' => '#ffffff',
        'button_style' => 'rounded',
        'font' => 'Inter',
        'progress_bar_style' => 'bar',
        'border_radius' => 8,
    ]);

    $result = $this->actingAs($admin)->deleteJson("/admin/themes/{$theme->id}");

    $result->assertOk();
    expect($result->json('html'))->not->toContain('Temp Theme');
    $this->assertDatabaseMissing('survey_themes', ['id' => $theme->id]);
});

test('a client portal user is redirected away from theme routes', function () {
    $clientUser = ClientUser::factory()->create();

    $this->actingAs($clientUser, 'client')->get('/admin/themes')->assertRedirect(route('admin.login'));
});
