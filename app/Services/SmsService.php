<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): bool
    {
        $settings = AppSetting::query()
            ->where('is_active', true)
            ->pluck('setting_value', 'setting_key')
            ->toArray();

        $apiKey = trim((string) ($settings['arkesel_api_key'] ?? ''));
        $senderId = trim((string) ($settings['arkesel_sender_id'] ?? ''));
        $baseUrl = rtrim((string) ($settings['arkesel_base_url'] ?? 'https://sms.arkesel.com'), '/');

        if ($apiKey === '' || $senderId === '') {
            Log::warning('SMS not sent: missing Arkesel configuration.', [
                'to' => $to,
                'message' => $message,
            ]);

            return false;
        }

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Accept' => 'application/json',
        ])->post($baseUrl . '/api/v2/sms/send', [
            'sender' => $senderId,
            'message' => $message,
            'recipients' => [$to],
            'sandbox' => false,
            'date_schedule' => null,
            ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('Arkesel SMS send failed.', [
            'to' => $to,
            'status' => $response->status(),
            'response' => $response->body(),
        ]);

        return false;
    }
}
