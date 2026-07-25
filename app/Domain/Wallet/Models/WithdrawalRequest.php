<?php

namespace App\Domain\Wallet\Models;

use App\Domain\Publisher\Models\PublisherProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'publisher_id',
        'amount',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'status',
        'rejection_reason',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(PublisherWallet::class, 'wallet_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(PublisherProfile::class, 'publisher_id');
    }
}
