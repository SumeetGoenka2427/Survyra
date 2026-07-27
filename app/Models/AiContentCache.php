<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiContentCache extends Model
{
    // Eloquent's default pluralized guess would be `ai_content_caches` - the
    // migration created the table as `ai_content_cache` (singular), which
    // silently broke every AI method that touches this cache.
    protected $table = 'ai_content_cache';

    protected $fillable = [
        'ai_related_type',
        'ai_related_id',
        'type',
        'input_context',
        'output_content',
        'token_count',
    ];

    protected function casts(): array
    {
        return [
            'input_context' => 'array',
            'output_content' => 'array',
            'token_count' => 'integer',
        ];
    }
}