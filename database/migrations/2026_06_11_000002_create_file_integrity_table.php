<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_integrity_checks', function (Blueprint $table) {
            $table->id();
            $table->string('file_path', 500);
            $table->string('expected_hash', 64);
            $table->string('status', 20)->default('clean');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            $table->unique('file_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_integrity_checks');
    }
};
