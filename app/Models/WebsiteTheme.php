<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebsiteTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_system',
        'client_id',
        'primary_color',
        'secondary_color',
        'background',
        'heading_font',
        'body_font',
        'header_style',
        'button_style',
        'border_radius',
        'container_width',
        'custom_css',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'border_radius' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return HasMany<Website, $this>
     */
    public function websites(): HasMany
    {
        return $this->hasMany(Website::class, 'theme_id');
    }
}
