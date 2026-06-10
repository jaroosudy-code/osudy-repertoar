<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('role_id');
            $table->boolean('is_invisible')->default(false)->after('last_seen_at');
            $table->timestamp('last_chat_seen_at')->nullable()->after('is_invisible');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_seen_at', 'is_invisible', 'last_chat_seen_at']);
        });
    }
};
