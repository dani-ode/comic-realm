<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Engagement\Models\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminCommentController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $status = $request->query('status');
        $search = $request->query('search');

        // comic:id,title,slug → untuk link /comics/{slug}
        // chapter:id,chapter_number,comic_id → untuk link /read/{comicSlug}/{chapterNumber}
        // chapter.comic:id,slug → comic slug milik chapter (bisa berbeda jika chapter_id saja yang ada)
        $query = Comment::with([
            'user:id,name,email',
            'comic:id,title,slug',
            'chapter:id,chapter_number,comic_id',
            'chapter.comic:id,slug',
        ]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('comment_text', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('comic', function ($cq) use ($search) {
                        $cq->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $comments = $query->latest()->paginate(15)->withQueryString();

        $metrics = [
            'total_comments' => Comment::count(),
            'published_comments' => Comment::where('status', 'published')->count(),
            'hidden_comments' => Comment::where('status', 'hidden')->count(),
            'flagged_comments' => Comment::where('status', 'flagged')->count(),
        ];

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
            'metrics' => $metrics,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function toggleStatus(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:published,hidden,flagged'],
        ]);

        $comment = Comment::findOrFail($id);
        $comment->update([
            'status' => $request->input('status'),
        ]);

        return redirect()->back()->with('success', "Status komentar berhasil diperbarui menjadi {$comment->status}.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return redirect()->back()->with('success', "Komentar berhasil dihapus.");
    }
}
