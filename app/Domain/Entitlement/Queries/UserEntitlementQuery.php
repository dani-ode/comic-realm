<?php

namespace App\Domain\Entitlement\Queries;

use App\Domain\Entitlement\Models\Entitlement;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserEntitlementQuery
{
    public function getLibraryPaginator(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return Entitlement::query()
            ->with(['comic', 'chapter'])
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->latest('granted_at')
            ->paginate($perPage);
    }
}
