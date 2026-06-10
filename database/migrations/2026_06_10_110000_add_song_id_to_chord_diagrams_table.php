<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chord_diagrams', function (Blueprint $table) {
            // Unikátnosť podľa samotného mena nestačí – to isté meno môže mať rôzny diagram per pieseň
            $table->dropUnique('chord_diagrams_name_unique');

            // Voliteľná väzba na pieseň (null = globálny predvolený diagram)
            $table->foreignId('song_id')->nullable()->after('id')
                  ->constrained('songs')->cascadeOnDelete();

            // Nový unikátny index: (name, song_id) — v MySQL NULL ≠ NULL, takže
            // globálne záznamy (song_id IS NULL) môžeme zabezpečiť aplikačne
            $table->index(['name', 'song_id'], 'chord_name_song_idx');
        });
    }

    public function down(): void
    {
        Schema::table('chord_diagrams', function (Blueprint $table) {
            $table->dropIndex('chord_name_song_idx');
            $table->dropForeign(['song_id']);
            $table->dropColumn('song_id');
            $table->unique('name');
        });
    }
};
