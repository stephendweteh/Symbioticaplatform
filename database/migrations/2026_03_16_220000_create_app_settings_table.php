<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50);
            $table->string('label');
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            [
                'category' => 'email_content',
                'label' => 'Registration Email Subject',
                'setting_key' => 'registration_email_subject',
                'setting_value' => 'Registration Confirmation',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'email_content',
                'label' => 'Registration Email Body',
                'setting_key' => 'registration_email_body',
                'setting_value' => 'Thank you for registering. Your 4-digit code is: {code}',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'smtp_connection',
                'label' => 'SMTP Host',
                'setting_key' => 'smtp_host',
                'setting_value' => '',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'smtp_connection',
                'label' => 'SMTP Port',
                'setting_key' => 'smtp_port',
                'setting_value' => '',
                'sort_order' => 11,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'smtp_connection',
                'label' => 'SMTP Username',
                'setting_key' => 'smtp_username',
                'setting_value' => '',
                'sort_order' => 12,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'smtp_connection',
                'label' => 'SMTP Password',
                'setting_key' => 'smtp_password',
                'setting_value' => '',
                'sort_order' => 13,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'smtp_connection',
                'label' => 'SMTP Encryption',
                'setting_key' => 'smtp_encryption',
                'setting_value' => 'tls',
                'sort_order' => 14,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'smtp_connection',
                'label' => 'Mail From Address',
                'setting_key' => 'mail_from_address',
                'setting_value' => '',
                'sort_order' => 15,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'smtp_connection',
                'label' => 'Mail From Name',
                'setting_key' => 'mail_from_name',
                'setting_value' => '',
                'sort_order' => 16,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
