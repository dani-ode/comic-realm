<?php

namespace App\Domain\Comic\Models;

use Database\Factories\ChapterPageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'page_number',
        'image_path',
        'image_url',
        'width',
        'height',
        'file_size',
        'mime_type',
    ];

    protected $appends = [
        'formatted_url',
    ];

    protected static function newFactory(): ChapterPageFactory
    {
        return ChapterPageFactory::new();
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function getFormattedUrlAttribute(): string
    {
        return $this->image_url ?? asset('storage/' . $this->image_path);
    }
}
