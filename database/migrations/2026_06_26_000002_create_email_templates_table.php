<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('template_key', 64)->unique();
            $table->string('subject', 255)->nullable();
            $table->string('greeting', 255)->nullable();
            $table->text('body')->nullable();
            $table->string('action_text', 255)->nullable();
            $table->string('action_url', 512)->nullable();
            $table->enum('level', ['primary', 'error'])->default('primary');
            $table->text('outro')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });

        $keys = [
            'account_created' => ['subject' => 'Account Created', 'greeting' => 'Hello {{name}}!', 'body' => "You are receiving this email because an account has been created for you on {{app_name}}.\nUsername: {{username}}\nEmail: {{email}}", 'action_text' => 'Setup Your Account', 'action_url' => '{{setup_url}}', 'level' => 'primary'],
            'password_reset' => ['subject' => 'Reset Password', 'greeting' => '', 'body' => "You are receiving this email because we received a password reset request for your account.", 'action_text' => 'Reset Password', 'action_url' => '{{reset_url}}', 'level' => 'primary'],
            'added_to_server' => ['subject' => 'Added to Server', 'greeting' => 'Hello {{name}}!', 'body' => "You have been added as a subuser for the following server, allowing you certain control over the server.\nServer Name: {{server_name}}", 'action_text' => 'Visit Server', 'action_url' => '{{server_url}}', 'level' => 'primary'],
            'removed_from_server' => ['subject' => 'Removed from Server', 'greeting' => 'Hello {{name}}!', 'body' => "You have been removed as a subuser for the following server.\nServer Name: {{server_name}}", 'action_text' => '', 'action_url' => '', 'level' => 'error'],
            'server_installed' => ['subject' => 'Server Installed', 'greeting' => 'Hello {{name}}!', 'body' => "Your server has finished installing and is now ready for use.\nServer Name: {{server_name}}", 'action_text' => 'Visit Server', 'action_url' => '{{server_url}}', 'level' => 'primary'],
            'mail_test' => ['subject' => 'Royal Panel Test Message', 'greeting' => 'Hey {{name}}!', 'body' => "This is a test message from your Royal Panel. If you are seeing this, your mail configuration is working correctly!", 'action_text' => '', 'action_url' => '', 'level' => 'primary'],
        ];

        foreach ($keys as $key => $data) {
            DB::table('email_templates')->insert([
                'template_key' => $key,
                'subject' => $data['subject'],
                'greeting' => $data['greeting'],
                'body' => $data['body'],
                'action_text' => $data['action_text'],
                'action_url' => $data['action_url'],
                'level' => $data['level'],
                'outro' => '',
                'enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
