<?php

namespace App\Domain\Reading\Actions;

use App\Domain\Reading\DTOs\ReadingProgressData;
use App\Domain\Reading\Models\ReadingProgress;
use App\Domain\User\Models\User;

class SaveReadingProgress
{
    public function execute(User $user, ReadingProgressData $data): ReadingProgress
    {
        return ReadingProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'chapter_id' => $data->chapter_id,
            ],
            [
                'comic_id' => $data->comic_id,
                'page_number' => $data->page_number,
                'progress_percent' => $data->progress_percent,
                'last_read_at' => now(),
            ]
        );
    }
}
