<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_2fa_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('discord_id');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->boolean('sent')->default(false);
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index(['discord_id', 'sent', 'used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discord_2fa_codes');
    }
};
