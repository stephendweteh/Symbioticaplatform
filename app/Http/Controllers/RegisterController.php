<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Member;
use App\Models\RegistrationField;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function __construct(
        protected SmsService $smsService
    ) {
    }

    public function showForm()
    {
        $registrationFields = RegistrationField::where('is_active', true)
            ->where('field_key', '!=', 'unique_code')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('register.index', compact('registrationFields'));
    }

    public function store(Request $request)
    {
        $registrationFields = RegistrationField::where('is_active', true)
            ->where('field_key', '!=', 'unique_code')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $dynamicRules = [];
        foreach ($registrationFields as $field) {
            $key = $field->field_key;
            $rules = [$field->is_required ? 'required' : 'nullable'];

            if (in_array($field->field_type, ['text', 'textarea'], true)) {
                $rules[] = 'string';
                $rules[] = 'max:1000';
            } elseif ($field->field_type === 'email') {
                $rules[] = 'email';
            } elseif ($field->field_type === 'tel') {
                $rules[] = 'string';
                $rules[] = 'max:50';
            } elseif ($field->field_type === 'number') {
                $rules[] = 'numeric';
            } elseif ($field->field_type === 'date') {
                $rules[] = 'date';
            } elseif ($field->field_type === 'select') {
                $rules[] = 'string';
                if (!empty($field->options)) {
                    $rules[] = 'in:' . implode(',', $field->options);
                }
            }

            if ($key === 'email') {
                $rules[] = 'email';
                $rules[] = 'max:255';
                $rules[] = 'unique:members,email';
            }

            if ($key === 'phone') {
                $rules[] = 'max:50';
                $rules[] = 'unique:members,phone';
            }

            $dynamicRules[$key] = $rules;
        }

        $dynamicData = $request->validate($dynamicRules);

        $uniqueCode = $this->generateUniqueCode();

        $memberColumns = ['full_name', 'email', 'phone', 'gender', 'organization', 'role'];
        $memberData = [];
        $additionalData = [];
        foreach ($dynamicData as $key => $value) {
            if (in_array($key, $memberColumns, true)) {
                $memberData[$key] = $value;
            } else {
                $additionalData[$key] = $value;
            }
        }

        if (!array_key_exists('full_name', $memberData) || blank($memberData['full_name'])) {
            $memberData['full_name'] = 'Member ' . now()->format('YmdHis');
        }
        if (!array_key_exists('email', $memberData) || blank($memberData['email'])) {
            $memberData['email'] = 'member_' . Str::lower(Str::random(10)) . '@example.local';
        }
        if (!array_key_exists('phone', $memberData) || blank($memberData['phone'])) {
            $memberData['phone'] = 'AUTO' . random_int(100000000, 999999999);
        }

        $member = Member::create([
            ...$memberData,
            'unique_code' => $uniqueCode,
            'additional_data' => $additionalData,
        ]);

        $settings = $this->getNotificationSettings();
        $this->applyRuntimeSmtpSettings($settings);

        $emailSubject = $this->cleanEmailSubject($settings['registration_email_subject'] ?? 'Registration Confirmation');
        $emailBodyTemplate = $settings['registration_email_body'] ?? 'Thank you for registering. Your 4-digit code is: {code}';
        $confirmationMessage = strtr($emailBodyTemplate, [
            '{name}' => (string) $member->full_name,
            '{email}' => (string) $member->email,
            '{phone}' => (string) $member->phone,
            '{code}' => $uniqueCode,
        ]);
        $logoUrlSetting = $settings['email_logo_url'] ?? null;

        try {
            Mail::send([], [], function ($message) use ($member, $emailSubject, $confirmationMessage, $logoUrlSetting) {
                $emailSubject = $this->cleanEmailSubject($emailSubject);
                $logoSrc = $this->resolveEmailLogoSrc($message, $logoUrlSetting);
                $confirmationHtml = $this->buildStyledEmailHtml($emailSubject, $confirmationMessage, $logoSrc);
                $message->to($member->email)->subject($emailSubject);
                $message->html($confirmationHtml);
            });
        } catch (\Throwable $exception) {
            Log::error('Registration confirmation email failed.', [
                'member_id' => $member->id,
                'email' => $member->email,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            $this->smsService->send($member->phone, $confirmationMessage);
        } catch (\Throwable $exception) {
            Log::error('Registration confirmation SMS failed.', [
                'member_id' => $member->id,
                'phone' => $member->phone,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('register.index')
            ->with('success', 'Registration successful. Kindly check your email or SMS for your code.');
    }

    protected function generateUniqueCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Member::where('unique_code', $code)->exists());

        return $code;
    }

    protected function getNotificationSettings(): array
    {
        return AppSetting::query()
            ->where('is_active', true)
            ->pluck('setting_value', 'setting_key')
            ->toArray();
    }

    protected function applyRuntimeSmtpSettings(array $settings): void
    {
        $host = trim((string) ($settings['smtp_host'] ?? ''));
        $port = trim((string) ($settings['smtp_port'] ?? ''));

        if ($host === '' || $port === '') {
            return;
        }

        $encryption = trim((string) ($settings['smtp_encryption'] ?? ''));
        $username = (string) ($settings['smtp_username'] ?? '');
        $password = (string) ($settings['smtp_password'] ?? '');
        $fromAddress = trim((string) ($settings['mail_from_address'] ?? ''));
        $fromName = trim((string) ($settings['mail_from_name'] ?? ''));

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) $port,
            'mail.mailers.smtp.encryption' => $encryption !== '' ? $encryption : null,
            'mail.mailers.smtp.username' => $username !== '' ? $username : null,
            'mail.mailers.smtp.password' => $password !== '' ? $password : null,
            'mail.mailers.smtp.timeout' => null,
            'mail.mailers.smtp.local_domain' => env('MAIL_EHLO_DOMAIN'),
        ]);

        if ($fromAddress !== '') {
            config([
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName !== '' ? $fromName : config('app.name'),
            ]);
        }

        app('mail.manager')->forgetMailers();
    }

    protected function buildStyledEmailHtml(string $subject, string $message, string $logoSrc): string
    {
        $safeSubject = e($subject);
        $safeMessage = nl2br(e($message));
        $safeLogoUrl = e($logoSrc);
        $safeAppName = e((string) config('app.name', 'Exhibition System'));

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeSubject}</title>
</head>
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">
        <tr>
            <td style="background:linear-gradient(180deg,#7c3aed 0%,#a78bfa 100%);padding:24px 20px;text-align:center;">
                <img src="{$safeLogoUrl}" alt="{$safeAppName} logo" style="max-width:180px;height:auto;display:inline-block;">
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <h2 style="margin:0 0 12px;font-size:22px;line-height:1.2;color:#1e1b4b;">{$safeSubject}</h2>
                <div style="font-size:15px;line-height:1.6;color:#334155;">{$safeMessage}</div>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 24px 22px;font-size:12px;color:#64748b;border-top:1px solid #e2e8f0;">
                This is an automated message.
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    protected function resolveEmailLogoSrc($message, ?string $logoUrlSetting): string
    {
        $logoUrlSetting = trim((string) $logoUrlSetting);
        if ($logoUrlSetting === '') {
            return $this->embedLogoOrFallback($message, public_path('logo.png'), url('/logo.png'));
        }

        if (str_starts_with($logoUrlSetting, 'http://') || str_starts_with($logoUrlSetting, 'https://')) {
            return $logoUrlSetting;
        }

        $relativePath = ltrim($logoUrlSetting, '/');
        $absolutePath = public_path($relativePath);

        return $this->embedLogoOrFallback($message, $absolutePath, url('/' . $relativePath));
    }

    protected function embedLogoOrFallback($message, string $absolutePath, string $fallbackSrc): string
    {
        if (! is_file($absolutePath)) {
            return $fallbackSrc;
        }

        try {
            return $message->embed($absolutePath);
        } catch (\Throwable) {
            return $fallbackSrc;
        }
    }

    protected function cleanEmailSubject(string $subject): string
    {
        $cleaned = preg_replace('/\[\s*test\s*\]\s*/i', '', $subject);
        $cleaned = preg_replace('/^\s*test\s*[:\-]\s*/i', '', (string) $cleaned);
        $cleaned = trim((string) $cleaned);

        return $cleaned !== '' ? $cleaned : 'Registration Confirmation';
    }
}
