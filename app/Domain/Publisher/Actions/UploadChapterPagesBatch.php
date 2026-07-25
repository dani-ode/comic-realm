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
     * @param Chapter $chapter
     * @param UploadedFile[] $files
     * @return ChapterPage[]
     */
    public function execute(Chapter $chapter, array $files): array
    {
        return DB::transaction(function () use ($chapter, $files) {
            $chapter->load('comic');
            $comicSlug = $chapter->comic->slug;
            $chapterNumber = $chapter->chapter_number;

            // Clear previous pages if re-uploading
            $chapter->pages()->delete();

            $createdPages = [];
            $pageNumber = 1;

            foreach ($files as $file) {
                $filename = sprintf('%03d_%s.webp', $pageNumber, uniqid());
                $directory = "comics/{$comicSlug}/ch{$chapterNumber}";

                $path = $file->storeAs($directory, $filename, 'public');

                $dimensions = @getimagesize($file->getRealPath());
                $width = $dimensions ? $dimensions[0] : 800;
                $height = $dimensions ? $dimensions[1] : 1200;

                $page = ChapterPage::create([
                    'chapter_id' => $chapter->id,
                    'page_number' => $pageNumber,
                    'image_path' => $path,
                    'image_url' => Storage::url($path),
                    'width' => $width,
                    'height' => $height,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getClientMimeType(),
                ]);

                $createdPages[] = $page;
                $pageNumber++;
            }

            return $createdPages;
        });
    }
}
