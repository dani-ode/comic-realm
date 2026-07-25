<?php

namespace App\Domain\Publisher\Models;

use App\Domain\Publisher\Enums\PublisherStatus;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublisherProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_name',
        'slug',
        'bio',
        'logo',
        'banner',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'verification_status',
        'rejection_reason',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'verification_status' => PublisherStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isApproved(): bool
    {
        return $this->verification_status === PublisherStatus::APPROVED;
    }
}
