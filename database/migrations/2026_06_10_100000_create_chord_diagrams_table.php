<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chord_diagrams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique();
            $table->json('frets');          // 6 hodnoty: -1=tlmená, 0=voľná, 1+=pražec
            $table->json('fingers');        // 6 hodnoty: 0=žiadny, 1-4=prst
            $table->tinyInteger('starting_fret')->default(1);
            $table->tinyInteger('barre_fret')->nullable();
            $table->tinyInteger('barre_from_string')->nullable(); // 0=low E, 5=high e
            $table->tinyInteger('barre_to_string')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chord_diagrams');
    }
};
