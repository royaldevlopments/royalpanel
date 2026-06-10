<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_geo_cache', function (Blueprint $table) {
            $table->string('ip', 45)->primary();
            $table->string('country_code', 2)->nullable();
            $table->string('country_name', 100)->nullable();
            $table->timestamp('cached_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_geo_cache');
    }
};
