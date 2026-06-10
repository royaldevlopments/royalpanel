<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45);
            $table->string('reason', 255)->nullable();
            $table->string('type', 20)->default('manual');
            $table->string('country_code', 2)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index('ip');
            $table->index('expires_at');
        });

        Schema::create('security_attack_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('severity', 20)->default('medium');
            $table->string('ip', 45)->nullable();
            $table->text('details')->nullable();
            $table->json('metadata')->nullable();
            $table->string('action_taken', 100)->nullable();
            $table->timestamp('detected_at');
            $table->timestamps();
            $table->index('type');
            $table->index('severity');
            $table->index('detected_at');
        });

        Schema::create('security_country_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2);
            $table->string('country_name', 100);
            $table->string('mode', 10)->default('block');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique('country_code');
        });

        Schema::create('security_rate_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45);
            $table->string('route', 255)->nullable();
            $table->string('method', 10)->nullable();
            $table->integer('attempts');
            $table->boolean('blocked')->default(false);
            $table->timestamp('logged_at');
            $table->index('ip');
            $table->index('logged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_rate_logs');
        Schema::dropIfExists('security_country_blocks');
        Schema::dropIfExists('security_attack_logs');
        Schema::dropIfExists('security_blocked_ips');
    }
};
