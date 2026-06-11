<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_discord_webhooks', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id')->unsigned()->unique();
            $table->string('url', 255);
            $table->json('events')->nullable();
            $table->timestamps();

            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_discord_webhooks');
    }
};
