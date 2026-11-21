<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'type',
        'product_name',
        'message',
        'details',
        'status',
        'admin_notes',
    ];

    /**
     * Cast attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    public function isContacted(): bool
    {
        return $this->status === 'contacted';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
