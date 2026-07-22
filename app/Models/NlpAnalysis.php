<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NlpAnalysis extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'survey_id',
        'type',
        'results',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'results' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}