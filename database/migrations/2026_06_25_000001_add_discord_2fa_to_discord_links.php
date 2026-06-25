<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discord_links', function (Blueprint $table) {
            $table->boolean('discord_2fa_enabled')->default(false)->after('linked_at');
        });
    }

    public function down(): void
    {
        Schema::table('discord_links', function (Blueprint $table) {
            $table->dropColumn('discord_2fa_enabled');
        });
    }
};
