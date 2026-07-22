<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiContentCache extends Model
{
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