<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comics', function (Blueprint $table) {
            $table->mediumText('cover_image')->change();
            $table->mediumText('banner_image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('comics', function (Blueprint $table) {
            $table->string('cover_image', 500)->change();
            $table->string('banner_image', 500)->nullable()->change();
        });
    }
};
