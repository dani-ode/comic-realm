<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('alternative_title', 255)->nullable();
            $table->text('description');
            $table->mediumText('cover_image');
            $table->mediumText('banner_image')->nullable();
            $table->string('author_name', 150)->nullable();
            $table->string('artist_name', 150)->nullable();
            $table->string('status', 30)->default('ongoing')->index(); // ongoing, completed, hiatus, cancelled
            $table->string('publication_status', 30)->default('draft')->index(); // draft, pending_review, published, rejected, archived
            $table->string('content_rating', 30)->default('all_ages'); // all_ages, teen, mature
            $table->string('language', 10)->default('id');
            $table->unsignedBigInteger('total_views')->default(0);
            $table->unsignedBigInteger('total_bookmarks')->default(0);
            $table->unsignedInteger('total_ratings')->default(0);
            $table->decimal('rating_average', 3, 2)->default(0.00);
            $table->unsignedInteger('total_comments')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('featured_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comics');
    }
};
