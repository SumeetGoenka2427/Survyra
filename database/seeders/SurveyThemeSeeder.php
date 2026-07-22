<?php

namespace Database\Seeders;

use App\Models\SurveyTheme;
use Illuminate\Database\Seeder;

class SurveyThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            ['name' => 'Healthcare', 'primary_color' => '#0f9d8c', 'secondary_color' => '#4a6572', 'background' => '#f4fbfa', 'button_style' => 'rounded', 'font' => 'Nunito Sans', 'progress_bar_style' => 'bar', 'border_radius' => 10],
            ['name' => 'Corporate', 'primary_color' => '#1f3a5f', 'secondary_color' => '#64748b', 'background' => '#ffffff', 'button_style' => 'square', 'font' => 'Inter', 'progress_bar_style' => 'steps', 'border_radius' => 4],
            ['name' => 'Cafe', 'primary_color' => '#8b5e34', 'secondary_color' => '#c98a3e', 'background' => '#fff8f0', 'button_style' => 'rounded', 'font' => 'Poppins', 'progress_bar_style' => 'dots', 'border_radius' => 12],
            ['name' => 'Luxury', 'primary_color' => '#d4af37', 'secondary_color' => '#e5e5e5', 'background' => '#141414', 'button_style' => 'pill', 'font' => 'Playfair Display', 'progress_bar_style' => 'bar', 'border_radius' => 2],
            ['name' => 'Minimal', 'primary_color' => '#111111', 'secondary_color' => '#6b7280', 'background' => '#ffffff', 'button_style' => 'square', 'font' => 'system-ui', 'progress_bar_style' => 'bar', 'border_radius' => 0],
            ['name' => 'Dark', 'primary_color' => '#6366f1', 'secondary_color' => '#a1a1aa', 'background' => '#18181b', 'button_style' => 'rounded', 'font' => 'Inter', 'progress_bar_style' => 'bar', 'border_radius' => 8],
            ['name' => 'Modern', 'primary_color' => '#ff6b6b', 'secondary_color' => '#4ecdc4', 'background' => '#ffffff', 'button_style' => 'pill', 'font' => 'Poppins', 'progress_bar_style' => 'dots', 'border_radius' => 16],
        ];

        foreach ($themes as $theme) {
            SurveyTheme::query()->updateOrCreate(
                ['name' => $theme['name'], 'is_system' => true],
                $theme
            );
        }
    }
}
