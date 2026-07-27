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
     * Supports image files, base64 strings, .txt files with URLs, and direct URL strings.
     */
    public function execute(Chapter $chapter, array|string|UploadedFile $files, ?int $insertAfterPage = null): array
    {
        return DB::transaction(function () use ($chapter, $files, $insertAfterPage) {
            $chapter->load('comic');
            $comicSlug = $chapter->comic->slug;
            $chapterNumber = $chapter->chapter_number;

            // Normalize $files input into an array of items (UploadedFile, Base64, or URL string)
            $items = [];
            $rawList = is_array($files) ? $files : [$files];

            foreach ($rawList as $item) {
                if ($item instanceof UploadedFile) {
                    $ext = strtolower($item->getClientOriginalExtension());
                    $mime = strtolower($item->getClientMimeType());

                    if ($ext === 'txt' || str_contains($mime, 'text/plain')) {
                        $lines = file($item->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($lines as $line) {
                            $trimmed = trim($line);
                            if (! empty($trimmed) && (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://'))) {
                                $items[] = $trimmed;
                            }
                        }
                    } else {
                        $items[] = $item;
                    }
                } elseif (is_string($item)) {
                    // Could be multi-line string or single string
                    $lines = explode("\n", str_replace("\r", "", $item));
                    foreach ($lines as $line) {
                        $trimmed = trim($line);
                        if (empty($trimmed)) {
                            continue;
                        }
                        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://') || str_starts_with($trimmed, 'data:image')) {
                            $items[] = $trimmed;
                        }
                    }
                }
            }

            $totalItemsCount = count($items);

            if ($totalItemsCount === 0) {
                return $chapter->pages()->orderBy('page_number', 'asc')->get()->toArray();
            }

            if ($insertAfterPage !== null && $insertAfterPage >= 0) {
                // Shift existing pages after insertAfterPage by +totalItemsCount
                ChapterPage::where('chapter_id', $chapter->id)
                    ->where('page_number', '>', $insertAfterPage)
                    ->orderBy('page_number', 'desc')
                    ->increment('page_number', $totalItemsCount);

                $startPageNumber = $insertAfterPage + 1;
            } else {
                $maxPage = ChapterPage::where('chapter_id', $chapter->id)->max('page_number') ?? 0;
                $startPageNumber = $maxPage + 1;
            }

            $createdPages = [];
            $pageNumber = $startPageNumber;

            foreach ($items as $item) {
                if ($item instanceof UploadedFile) {
                    $filename = sprintf('%03d_%s.webp', $pageNumber, uniqid());
                    $directory = "comics/{$comicSlug}/ch{$chapterNumber}";
                    $path = $item->storeAs($directory, $filename, 'public');

                    $dimensions = @getimagesize($item->getRealPath());
                    $width = $dimensions ? $dimensions[0] : 800;
                    $height = $dimensions ? $dimensions[1] : 1200;
                    $fileSize = $item->getSize();
                    $mimeType = $item->getClientMimeType();
                    $imageUrl = Storage::url($path);
                } elseif (is_string($item) && str_starts_with($item, 'data:image')) {
                    // Handle Base64 Upload
                    preg_match('/data:image\/(.*?);base64,(.*)/', $item, $matches);
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
                } elseif (is_string($item) && (str_starts_with($item, 'http://') || str_starts_with($item, 'https://'))) {
                    // Handle External URL
                    $path = 'external';
                    $imageUrl = $item;
                    $width = 800;
                    $height = 1200;
                    $fileSize = null;
                    $mimeType = 'image/webp';
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

    /**
     * Delete all pages in a chapter.
     */
    public function deleteAllPages(Chapter $chapter): array
    {
        return DB::transaction(function () use ($chapter) {
            $pages = ChapterPage::where('chapter_id', $chapter->id)->get();

            foreach ($pages as $page) {
                if ($page->image_path && $page->image_path !== 'external' && Storage::disk('public')->exists($page->image_path)) {
                    Storage::disk('public')->delete($page->image_path);
                }
                $page->delete();
            }

            return [];
        });
    }
}
