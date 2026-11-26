<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerOrder extends Model
{
    protected $fillable = [
        'order_code',
        'customer_id',
        'status',
        'payment_method',
        'payment_reference',
        'payment_status',
        'amount_tendered',
        'change_due',
        'total_price',
        'stock_reserved',
    ];

    protected function casts(): array
    {
        return [
            'amount_tendered' => 'decimal:2',
            'change_due' => 'decimal:2',
            'total_price' => 'decimal:2',
            'stock_reserved' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerOrderItem::class);
    }
}