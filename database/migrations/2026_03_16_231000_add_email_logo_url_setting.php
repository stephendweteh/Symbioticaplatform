<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('app_settings')->where('setting_key', 'email_logo_url')->exists();
        if (! $exists) {
            DB::table('app_settings')->insert([
                'category' => 'email_content',
                'label' => 'Email Logo URL',
                'setting_key' => 'email_logo_url',
                'setting_value' => '/logo.png',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('app_settings')->where('setting_key', 'email_logo_url')->delete();
    }
};
