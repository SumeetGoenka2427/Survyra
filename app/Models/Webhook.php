<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    protected $fillable = [
        'client_id', 'survey_id', 'url', 'events', 'secret',
        'is_active', 'last_triggered_at', 'failure_count',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function survey(): BelongsTo { return $this->belongsTo(Survey::class); }
    public function deliveries(): HasMany { return $this->hasMany(WebhookDelivery::class); }
}
