<?php

namespace App\Domain\Comic\Models;

use App\Domain\Comic\Enums\ChapterStatus;
use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chapter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'comic_id',
        'title',
        'slug',
        'chapter_number',
        'description',
        'is_free',
        'price',
        'currency',
        'status',
        'total_views',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'chapter_number' => 'float',
            'is_free' => 'boolean',
            'price' => 'integer',
            'status' => ChapterStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ChapterFactory
    {
        return ChapterFactory::new();
    }

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(ChapterPage::class)->orderBy('page_number', 'asc');
    }
}
