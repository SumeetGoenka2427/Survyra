<?php

namespace App\Models;

use App\Contracts\SectionTypeContract;
use App\Services\SectionTypeRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SectionType extends Model
{
    protected $fillable = [
        'key',
        'label',
        'category',
        'settings_schema',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings_schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The SectionTypeContract implementation this row is backed by.
     */
    public function contract(): SectionTypeContract
    {
        return app(SectionTypeRegistry::class)->resolve($this->key);
    }

    /**
     * @return HasMany<WebsiteSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(WebsiteSection::class);
    }
}
