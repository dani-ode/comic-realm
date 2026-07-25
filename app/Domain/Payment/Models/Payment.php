<?php

namespace App\Domain\Payment\Models;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'user_id',
        'tripay_reference',
        'merchant_ref',
        'payment_method',
        'payment_name',
        'amount',
        'fee_merchant',
        'fee_customer',
        'total_fee',
        'amount_received',
        'pay_code',
        'pay_url',
        'checkout_url',
        'status',
        'instructions',
        'paid_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'fee_merchant' => 'integer',
            'fee_customer' => 'integer',
            'total_fee' => 'integer',
            'amount_received' => 'integer',
            'status' => PaymentStatus::class,
            'instructions' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
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

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }
}
