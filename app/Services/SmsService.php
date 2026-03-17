<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): bool
    {
        $sid = (string) config('services.twilio.account_sid');
        $token = (string) config('services.twilio.auth_token');
        $from = (string) config('services.twilio.from');

        if ($sid === '' || $token === '' || $from === '') {
            Log::warning('SMS not sent: missing Twilio configuration.', [
                'to' => $to,
                'message' => $message,
            ]);

            return false;
        }

        $response = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $to,
                'From' => $from,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('SMS send failed.', [
            'to' => $to,
            'status' => $response->status(),
            'response' => $response->body(),
        ]);

        return false;
    }
}
