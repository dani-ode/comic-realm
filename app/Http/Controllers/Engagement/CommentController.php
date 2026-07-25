<?php

namespace App\Http\Controllers\Engagement;

use App\Domain\Engagement\Actions\PostComment;
use App\Domain\Engagement\Models\Comment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Engagement\PostCommentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'comic_id' => ['required', 'integer', 'exists:comics,id'],
            'chapter_id' => ['nullable', 'integer', 'exists:chapters,id'],
        ]);

        $comments = Comment::with(['user:id,name,username,avatar', 'replies.user:id,name,username,avatar'])
            ->where('comic_id', $request->input('comic_id'))
            ->when($request->filled('chapter_id'), fn ($q) => $q->where('chapter_id', $request->input('chapter_id')))
            ->whereNull('parent_id')
            ->where('status', 'published')
            ->latest()
            ->paginate(15);

        return response()->json($comments);
    }

    public function store(PostCommentRequest $request, PostComment $postComment): JsonResponse
    {
        $comment = $postComment->execute($request->user(), $request->toDTO());
        $comment->load(['user:id,name,username,avatar']);

        return response()->json([
            'success' => true,
            'data' => $comment,
            'message' => 'Komentar berhasil dipublikasikan.',
        ]);
    }
}
