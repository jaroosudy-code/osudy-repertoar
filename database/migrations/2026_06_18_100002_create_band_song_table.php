<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('band_song', function (Blueprint $table) {
            $table->id();
            $table->foreignId('band_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->foreignId('added_by_user_id')->nullable()->nullOnDelete()->constrained('users');
            $table->timestamps();

            $table->unique(['band_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('band_song');
    }
};
