<?php

namespace Database\Seeders;

use App\Models\WebsiteTheme;
use Illuminate\Database\Seeder;

class WebsiteThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            ['name' => 'Healthcare', 'primary_color' => '#0f9d8c', 'secondary_color' => '#4a6572', 'background' => '#f4fbfa', 'heading_font' => 'Nunito Sans', 'body_font' => 'system-ui', 'header_style' => 'centered', 'button_style' => 'rounded', 'border_radius' => 10, 'container_width' => 'boxed'],
            ['name' => 'Corporate', 'primary_color' => '#1f3a5f', 'secondary_color' => '#64748b', 'background' => '#ffffff', 'heading_font' => 'Inter', 'body_font' => 'system-ui', 'header_style' => 'split', 'button_style' => 'square', 'border_radius' => 2, 'container_width' => 'boxed'],
            ['name' => 'Minimal', 'primary_color' => '#111111', 'secondary_color' => '#6b7280', 'background' => '#ffffff', 'heading_font' => 'system-ui', 'body_font' => 'system-ui', 'header_style' => 'centered', 'button_style' => 'square', 'border_radius' => 0, 'container_width' => 'boxed'],
            ['name' => 'Modern', 'primary_color' => '#4338ca', 'secondary_color' => '#64748b', 'background' => '#ffffff', 'heading_font' => 'Inter', 'body_font' => 'system-ui', 'header_style' => 'centered', 'button_style' => 'rounded', 'border_radius' => 10, 'container_width' => 'boxed'],
            ['name' => 'Restaurant', 'primary_color' => '#c1440e', 'secondary_color' => '#8b5e34', 'background' => '#fff8f0', 'heading_font' => 'Poppins', 'body_font' => 'system-ui', 'header_style' => 'centered', 'button_style' => 'pill', 'border_radius' => 14, 'container_width' => 'full'],
            ['name' => 'Luxury', 'primary_color' => '#b8860b', 'secondary_color' => '#1a1a1a', 'background' => '#faf7f2', 'heading_font' => 'Playfair Display', 'body_font' => 'system-ui', 'header_style' => 'centered', 'button_style' => 'pill', 'border_radius' => 2, 'container_width' => 'boxed'],
            ['name' => 'Dark', 'primary_color' => '#6366f1', 'secondary_color' => '#a1a1aa', 'background' => '#18181b', 'heading_font' => 'Inter', 'body_font' => 'system-ui', 'header_style' => 'centered', 'button_style' => 'rounded', 'border_radius' => 8, 'container_width' => 'boxed'],
            [
                'name' => 'Gradient', 'primary_color' => '#667eea', 'secondary_color' => '#764ba2', 'background' => '#f5f3ff',
                'heading_font' => 'Poppins', 'body_font' => 'system-ui', 'header_style' => 'centered', 'button_style' => 'pill',
                'border_radius' => 16, 'container_width' => 'full',
                // The theme system's only "escape hatch" for backgrounds beyond a
                // flat color - deliberately reused rather than adding a schema
                // column for one theme's gradient.
                'custom_css' => 'body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }',
            ],
        ];

        foreach ($themes as $theme) {
            WebsiteTheme::query()->updateOrCreate(
                ['name' => $theme['name'], 'is_system' => true],
                $theme
            );
        }

        // Retire the old 3-theme set's stale names if this seeder previously
        // ran with different names and nothing has since selected them.
        WebsiteTheme::query()
            ->where('is_system', true)
            ->whereNotIn('name', collect($themes)->pluck('name'))
            ->whereDoesntHave('websites')
            ->delete();
    }
}
