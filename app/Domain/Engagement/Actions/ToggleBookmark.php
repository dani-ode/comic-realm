<?php

namespace App\Domain\Engagement\Actions;

use App\Domain\Comic\Models\Comic;
use App\Domain\Engagement\Models\Bookmark;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class ToggleBookmark
{
    public function execute(User $user, int $comicId): bool
    {
        return DB::transaction(function () use ($user, $comicId) {
            $comic = Comic::findOrFail($comicId);

            $existing = Bookmark::where('user_id', $user->id)
                ->where('comic_id', $comic->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $comic->decrement('total_bookmarks');
                return false; // Unbookmarked
            }

            Bookmark::create([
                'user_id' => $user->id,
                'comic_id' => $comic->id,
                'notify_updates' => true,
            ]);

            $comic->increment('total_bookmarks');
            return true; // Bookmarked
        });
    }
}
