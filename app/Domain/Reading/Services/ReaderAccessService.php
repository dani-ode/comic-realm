<?php

namespace App\Domain\Reading\Services;

use App\Domain\Comic\Models\Chapter;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class ReaderAccessService
{
    public function canRead(?User $user, Chapter $chapter): bool
    {
        // 1. Chapter gratis dapat dibaca siapa saja
        if ($chapter->is_free) {
            return true;
        }

        // 2. Jika bab berbayar dan pengguna belum login -> Tolak
        if (! $user) {
            return false;
        }

        // 3. Admin atau Publisher pemilik komik memiliki akses penuh
        if ($user->isAdmin() || $user->id === $chapter->comic->publisher_id) {
            return true;
        }

        // 4. Periksa apakah pengguna memiliki Entitlement hak baca aktif
        return DB::table('entitlements')
            ->where('user_id', $user->id)
            ->where('chapter_id', $chapter->id)
            ->whereNull('revoked_at')
            ->exists();
    }
}
