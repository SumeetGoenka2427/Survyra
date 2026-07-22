<?php

namespace App\Services;

use App\Models\Client;
use App\Models\SurveyTheme;
use Illuminate\Database\Eloquent\Collection;

class SurveyThemeService
{
    public function all(?string $search = null): Collection
    {
        return SurveyTheme::query()
            ->withCount('surveys')
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): SurveyTheme
    {
        return SurveyTheme::query()->findOrFail($id);
    }

    /**
     * Themes selectable for a given client's survey: system themes plus any
     * custom themes already duplicated for this specific client.
     */
    public function availableFor(int $clientId): Collection
    {
        return SurveyTheme::query()
            ->where(fn ($query) => $query->where('is_system', true)->orWhere('client_id', $clientId))
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();
    }

    public function duplicateForClient(SurveyTheme $theme, Client $client): SurveyTheme
    {
        return SurveyTheme::query()->create([
            'name' => "{$theme->name} - {$client->company_name}",
            'is_system' => false,
            'client_id' => $client->id,
            'logo_path' => $theme->logo_path,
            'primary_color' => $theme->primary_color,
            'secondary_color' => $theme->secondary_color,
            'background' => $theme->background,
            'button_style' => $theme->button_style,
            'font' => $theme->font,
            'progress_bar_style' => $theme->progress_bar_style,
            'border_radius' => $theme->border_radius,
            'custom_css' => $theme->custom_css,
        ]);
    }

    public function create(array $data): SurveyTheme
    {
        return SurveyTheme::query()->create($data);
    }

    public function update(SurveyTheme $theme, array $data): SurveyTheme
    {
        $theme->update($data);

        return $theme;
    }

    public function delete(SurveyTheme $theme): void
    {
        $theme->delete();
    }
}
