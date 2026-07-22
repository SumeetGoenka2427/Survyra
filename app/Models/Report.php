<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'client_id',
        'survey_id',
        'type',
        'frequency',
        'recipients',
        'last_sent_at',
        'next_run_at',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'last_sent_at' => 'datetime',
            'next_run_at' => 'datetime',
            'is_active' => 'boolean',
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
     * @return BelongsTo<Survey, $this>
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Nullable - a client portal user can create a report too, but
     * `created_by` only references the internal `users` table (admin
     * audit trail), so it's simply left null in that case.
     *
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
