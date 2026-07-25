<?php

namespace App\Domain\Order\Models;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'comic_id',
        'chapter_id',
        'title_snapshot',
        'chapter_number_snapshot',
        'price',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'chapter_number_snapshot' => 'float',
            'price' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
