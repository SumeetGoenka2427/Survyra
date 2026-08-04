<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'demo', 'trial', 'won', 'lost'];
    public const PREFERRED_CONTACTS = ['whatsapp', 'phone', 'email'];
    public const INTERESTS = ['surveys', 'feedback', 'reviews', 'analytics'];

    protected $fillable = [
        'name',
        'business_name',
        'category',
        'phone',
        'email',
        'message',
        'preferred_contact',
        'interests',
        'source',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'interests' => 'array',
        ];
    }
}
