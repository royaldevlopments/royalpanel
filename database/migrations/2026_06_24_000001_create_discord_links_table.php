<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_links', function (Blueprint $table) {
            $table->id();
            $table->string('discord_id')->nullable()->unique();
            $table->integer('user_id')->unsigned()->unique();
            $table->string('code', 6)->nullable()->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discord_links');
    }
};
