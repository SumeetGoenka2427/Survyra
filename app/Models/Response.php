<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Response extends Model
{
    protected $fillable = [
        'client_id',
        'survey_id',
        'contact_id',
        'campaign_id',
        'respondent_identifier',
        'status',
        'device',
        'browser',
        'ip',
        'source',
        'started_at',
        'completed_at',
        'score',
        'sentiment',
        'last_question_id',
        'drop_off_at',
        'country',
        'city',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'drop_off_at' => 'datetime',
            'score' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Response $response) {
            $response->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<Survey, $this>
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * @return HasMany<ResponseAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ResponseAnswer::class);
    }

    /**
     * @return HasMany<ResponseUpload, $this>
     */
    public function uploads(): HasMany
    {
        return $this->hasMany(ResponseUpload::class);
    }
}
