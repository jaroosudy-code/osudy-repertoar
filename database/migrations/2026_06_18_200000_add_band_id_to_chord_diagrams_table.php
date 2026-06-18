<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chord_diagrams', function (Blueprint $table) {
            $table->foreignId('band_id')->nullable()->after('song_id')->nullOnDelete()->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('chord_diagrams', function (Blueprint $table) {
            $table->dropForeign(['band_id']);
            $table->dropColumn('band_id');
        });
    }
};
