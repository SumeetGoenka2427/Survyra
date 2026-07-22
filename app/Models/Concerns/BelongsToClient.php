<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Scopes a model to the currently authenticated client portal user's client_id.
 * Internal staff (web guard) are never scoped - they explicitly filter by client_id
 * in their own controllers when they need to.
 */
trait BelongsToClient
{
    protected static function bootBelongsToClient(): void
    {
        static::addGlobalScope('client', function (Builder $builder) {
            if (Auth::guard('client')->check()) {
                $builder->where($builder->getModel()->getTable().'.client_id', Auth::guard('client')->user()->client_id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->client_id) && Auth::guard('client')->check()) {
                $model->client_id = Auth::guard('client')->user()->client_id;
            }
        });
    }

    /**
     * @return BelongsTo<\App\Models\Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
