<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained('comics')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->decimal('chapter_number', 8, 2)->index(); // 1, 1.5, 2, etc.
            $table->text('description')->nullable();
            $table->boolean('is_free')->default(true)->index();
            $table->unsignedBigInteger('price')->default(0); // Price in IDR without decimal
            $table->char('currency', 3)->default('IDR');
            $table->string('status', 30)->default('draft')->index(); // draft, pending_review, published, rejected
            $table->unsignedBigInteger('total_views')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
