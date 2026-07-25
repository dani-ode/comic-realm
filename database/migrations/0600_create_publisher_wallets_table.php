<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publisher_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('publisher_profiles')->cascadeOnDelete();
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedBigInteger('total_earned')->default(0);
            $table->unsignedBigInteger('total_withdrawn')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publisher_wallets');
    }
};
