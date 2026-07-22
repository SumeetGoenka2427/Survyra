<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortLink extends Model
{
    protected $fillable = [
        'code',
        'target_url',
        'click_count',
        'last_clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'last_clicked_at' => 'datetime',
        ];
    }

    public function registerClick(): void
    {
        $this->increment('click_count');
        $this->update(['last_clicked_at' => now()]);
    }
}
