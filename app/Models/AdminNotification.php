<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminNotification extends Model
{
    protected $fillable = [
        'event_key',
        'type',
        'event_name',
        'subject_type',
        'subject_id',
        'title',
        'message',
        'detail',
        'url',
        'payload',
        'occurred_at',
    ];

    /**
     * Cast attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AdminNotificationRead::class);
    }
}
