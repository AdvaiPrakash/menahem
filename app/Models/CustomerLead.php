<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_type',
        'name',
        'email',
        'phone',
        'delivery_address',
        'city',
        'state',
        'pin_code',
        'customer_gstin',
        'registration_date',
        'first_purchase_date',
        'last_purchase_date',
        'total_orders',
        'total_purchase_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'datetime',
            'first_purchase_date' => 'datetime',
            'last_purchase_date' => 'datetime',
            'total_orders' => 'integer',
            'total_purchase_amount' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_email', 'email');
    }
}
