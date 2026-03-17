<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            [
                'category' => 'sms_connection',
                'label' => 'Arkesel API Key',
                'setting_key' => 'arkesel_api_key',
                'setting_value' => '',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'category' => 'sms_connection',
                'label' => 'Arkesel Sender ID',
                'setting_key' => 'arkesel_sender_id',
                'setting_value' => '',
                'sort_order' => 21,
                'is_active' => true,
            ],
            [
                'category' => 'sms_connection',
                'label' => 'Arkesel Base URL',
                'setting_key' => 'arkesel_base_url',
                'setting_value' => 'https://sms.arkesel.com',
                'sort_order' => 22,
                'is_active' => true,
            ],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('app_settings')->where('setting_key', $row['setting_key'])->exists();
            if (! $exists) {
                DB::table('app_settings')->insert([
                    ...$row,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('app_settings')->whereIn('setting_key', [
            'arkesel_api_key',
            'arkesel_sender_id',
            'arkesel_base_url',
        ])->delete();
    }
};
