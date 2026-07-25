<?php

namespace App\Domain\Reading\Actions;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\Reading\Models\ComicView;
use App\Domain\User\Models\User;
use Illuminate\Http\Request;

class TrackComicView
{
    public function execute(Comic $comic, ?Chapter $chapter = null, ?User $user = null, ?Request $request = null): void
    {
        $ip = $request ? $request->ip() : null;
        $ipHash = $ip ? md5($ip) : null;
        $visitorId = $request ? $request->cookie('visitor_id') : null;
        $userAgeHash = $request ? md5($request->userAgent() ?? '') : null;

        // Pengecekan unit window 30 menit (Cegah F5 spam refresh)
        $recentView = ComicView::query()
            ->where('comic_id', $comic->id)
            ->when($chapter, fn ($q) => $q->where('chapter_id', $chapter->id))
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->when(! $user && $ipHash, fn ($q) => $q->where('ip_hash', $ipHash))
            ->where('viewed_at', '>=', now()->subMinutes(30))
            ->exists();

        if (! $recentView) {
            ComicView::create([
                'comic_id' => $comic->id,
                'chapter_id' => $chapter?->id,
                'user_id' => $user?->id,
                'visitor_id' => $visitorId,
                'ip_hash' => $ipHash,
                'user_agent_hash' => $userAgeHash,
                'viewed_at' => now(),
            ]);

            // Increment view counter cache
            $comic->increment('total_views');
            if ($chapter) {
                $chapter->increment('total_views');
            }
        }
    }
}
