<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_system',
        'client_id',
        'logo_path',
        'primary_color',
        'secondary_color',
        'background',
        'button_style',
        'font',
        'progress_bar_style',
        'border_radius',
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
     * @return HasMany<Survey, $this>
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class, 'theme_id');
    }
}
