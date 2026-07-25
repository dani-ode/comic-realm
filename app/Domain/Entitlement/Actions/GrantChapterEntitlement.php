<?php

namespace App\Domain\Entitlement\Actions;

use App\Domain\Entitlement\Models\Entitlement;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class GrantChapterEntitlement
{
    public function execute(User $user, int $comicId, int $chapterId, ?int $orderId = null): Entitlement
    {
        return DB::transaction(function () use ($user, $comicId, $chapterId, $orderId) {
            return Entitlement::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'chapter_id' => $chapterId,
                ],
                [
                    'comic_id' => $comicId,
                    'order_id' => $orderId,
                    'granted_at' => now(),
                    'revoked_at' => null,
                ]
            );
        });
    }
}
