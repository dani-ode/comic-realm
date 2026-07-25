<?php

namespace App\Domain\Engagement\Actions;

use App\Domain\Comic\Models\Comic;
use App\Domain\Engagement\DTOs\PostCommentData;
use App\Domain\Engagement\Models\Comment;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class PostComment
{
    public function execute(User $user, PostCommentData $data): Comment
    {
        return DB::transaction(function () use ($user, $data) {
            $comic = Comic::findOrFail($data->comic_id);

            $comment = Comment::create([
                'user_id' => $user->id,
                'comic_id' => $comic->id,
                'chapter_id' => $data->chapter_id,
                'parent_id' => $data->parent_id,
                'comment_text' => $data->comment_text,
                'is_spoiler' => $data->is_spoiler,
                'status' => 'published',
            ]);

            $comic->increment('total_comments');

            return $comment;
        });
    }
}
