<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Deliberately does NOT use the BelongsToClient trait: that trait's global scope
 * resolves Auth::guard('client')->user(), which itself queries this model via
 * EloquentUserProvider - self-scoping here would recurse infinitely.
 */
class ClientUser extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'invited_by',
        'invitation_token',
        'invitation_accepted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isOwner(): bool { return $this->role === 'owner'; }
    public function isEditor(): bool { return in_array($this->role, ['owner', 'editor']); }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
