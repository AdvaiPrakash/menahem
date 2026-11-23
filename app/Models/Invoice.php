<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'invoice_number',
        'invoice_date',
        'user_id',
        'customer_type',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'billing_address',
        'customer_gstin',
        'seller_name',
        'seller_gstin',
        'seller_address',
        'seller_state',
        'seller_state_code',
        'subtotal',
        'discount',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'shipping_charges',
        'gateway_charges',
        'grand_total',
        'amount_paid',
        'payment_method',
        'payment_reference',
        'payment_status',
        'pdf_path',
        'pdf_token',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'subtotal' => 'float',
            'discount' => 'float',
            'taxable_amount' => 'float',
            'cgst_amount' => 'float',
            'sgst_amount' => 'float',
            'igst_amount' => 'float',
            'shipping_charges' => 'float',
            'gateway_charges' => 'float',
            'grand_total' => 'float',
            'amount_paid' => 'float',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
