<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('comic_id')->constrained('comics')->restrictOnDelete();
            $table->foreignId('chapter_id')->constrained('chapters')->restrictOnDelete();
            $table->string('title_snapshot', 255);
            $table->decimal('chapter_number_snapshot', 8, 2);
            $table->unsignedBigInteger('price');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
