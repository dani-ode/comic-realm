<?php

namespace App\Domain\Comic\Models;

use App\Domain\Comic\Enums\ComicPublicationStatus;
use App\Domain\Comic\Enums\ComicStatus;
use App\Domain\Comic\Enums\ContentRating;
use App\Domain\User\Models\User;
use Database\Factories\ComicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'publisher_id',
        'title',
        'slug',
        'alternative_title',
        'description',
        'cover_image',
        'banner_image',
        'author_name',
        'artist_name',
        'status',
        'publication_status',
        'content_rating',
        'language',
        'total_views',
        'total_bookmarks',
        'total_ratings',
        'rating_average',
        'total_comments',
        'is_featured',
        'featured_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ComicStatus::class,
            'publication_status' => ComicPublicationStatus::class,
            'content_rating' => ContentRating::class,
            'rating_average' => 'float',
            'is_featured' => 'boolean',
            'featured_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ComicFactory
    {
        return ComicFactory::new();
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'comic_genre');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('chapter_number', 'asc');
    }

    public function publishedChapters(): HasMany
    {
        return $this->hasMany(Chapter::class)
            ->where('status', 'published')
            ->orderBy('chapter_number', 'asc');
    }
}
