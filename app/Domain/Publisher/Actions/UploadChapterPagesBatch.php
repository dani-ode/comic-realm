<?php

namespace App\Domain\Publisher\Actions;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\ChapterPage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UploadChapterPagesBatch
{
    /**
     * Upload or insert new pages into a chapter.
     */
    public function execute(Chapter $chapter, array $files, ?int $insertAfterPage = null): array
    {
        return DB::transaction(function () use ($chapter, $files, $insertAfterPage) {
            $chapter->load('comic');
            $comicSlug = $chapter->comic->slug;
            $chapterNumber = $chapter->chapter_number;

            $fileCount = count($files);

            if ($insertAfterPage !== null && $insertAfterPage >= 0) {
                // Shift existing pages after insertAfterPage by +fileCount
                ChapterPage::where('chapter_id', $chapter->id)
                    ->where('page_number', '>', $insertAfterPage)
                    ->orderBy('page_number', 'desc')
                    ->increment('page_number', $fileCount);

                $startPageNumber = $insertAfterPage + 1;
            } else {
                $maxPage = ChapterPage::where('chapter_id', $chapter->id)->max('page_number') ?? 0;
                $startPageNumber = $maxPage + 1;
            }

            $createdPages = [];
            $pageNumber = $startPageNumber;

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $filename = sprintf('%03d_%s.webp', $pageNumber, uniqid());
                    $directory = "comics/{$comicSlug}/ch{$chapterNumber}";
                    $path = $file->storeAs($directory, $filename, 'public');

                    $dimensions = @getimagesize($file->getRealPath());
                    $width = $dimensions ? $dimensions[0] : 800;
                    $height = $dimensions ? $dimensions[1] : 1200;
                    $fileSize = $file->getSize();
                    $mimeType = $file->getClientMimeType();
                    $imageUrl = Storage::url($path);
                } else if (is_string($file) && str_starts_with($file, 'data:image')) {
                    // Handle Base64 Upload
                    preg_match('/data:image\/(.*?);base64,(.*)/', $file, $matches);
                    $extension = $matches[1] ?? 'webp';
                    $data = base64_decode($matches[2] ?? '');

                    $filename = sprintf('%03d_%s.%s', $pageNumber, uniqid(), $extension);
                    $directory = "comics/{$comicSlug}/ch{$chapterNumber}";
                    $path = "{$directory}/{$filename}";

                    Storage::disk('public')->put($path, $data);

                    $imageUrl = Storage::url($path);
                    $width = 800;
                    $height = 1200;
                    $fileSize = strlen($data);
                    $mimeType = "image/{$extension}";
                } else {
                    continue;
                }

                $page = ChapterPage::create([
                    'chapter_id' => $chapter->id,
                    'page_number' => $pageNumber,
                    'image_path' => $path,
                    'image_url' => $imageUrl,
                    'width' => $width,
                    'height' => $height,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                ]);

                $createdPages[] = $page;
                $pageNumber++;
            }

            return $chapter->pages()->orderBy('page_number', 'asc')->get()->toArray();
        });
    }

    /**
     * Delete a single page and re-index remaining pages.
     */
    public function deletePage(Chapter $chapter, int $pageId): array
    {
        return DB::transaction(function () use ($chapter, $pageId) {
            $page = ChapterPage::where('id', $pageId)
                ->where('chapter_id', $chapter->id)
                ->firstOrFail();

            if ($page->image_path && Storage::disk('public')->exists($page->image_path)) {
                Storage::disk('public')->delete($page->image_path);
            }

            $page->delete();

            // Re-index remaining pages sequentially (1, 2, 3...)
            $remainingPages = ChapterPage::where('chapter_id', $chapter->id)
                ->orderBy('page_number', 'asc')
                ->get();

            $idx = 1;
            foreach ($remainingPages as $p) {
                if ($p->page_number !== $idx) {
                    $p->update(['page_number' => $idx]);
                }
                $idx++;
            }

            return ChapterPage::where('chapter_id', $chapter->id)
                ->orderBy('page_number', 'asc')
                ->get()
                ->toArray();
        });
    }

    /**
     * Reorder pages given an array of page IDs in the new order.
     */
    public function reorderPages(Chapter $chapter, array $pageIds): array
    {
        return DB::transaction(function () use ($chapter, $pageIds) {
            $pageNumber = 1;

            foreach ($pageIds as $pageId) {
                ChapterPage::where('id', $pageId)
                    ->where('chapter_id', $chapter->id)
                    ->update(['page_number' => $pageNumber]);

                $pageNumber++;
            }

            return ChapterPage::where('chapter_id', $chapter->id)
                ->orderBy('page_number', 'asc')
                ->get()
                ->toArray();
        });
    }
}
