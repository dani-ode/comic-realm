<?php

namespace App\Domain\Wallet\Models;

use App\Domain\Publisher\Models\PublisherProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublisherWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'publisher_id',
        'balance',
        'total_earned',
        'total_withdrawn',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'total_earned' => 'integer',
            'total_withdrawn' => 'integer',
        ];
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(PublisherProfile::class, 'publisher_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class, 'wallet_id');
    }
}
