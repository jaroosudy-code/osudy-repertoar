<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->string('author_lyrics', 255)->nullable()->after('notes');
            $table->string('author_music', 255)->nullable()->after('author_lyrics');
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn(['author_lyrics', 'author_music']);
        });
    }
};
