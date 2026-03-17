<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('category');

        return view('admin.settings.index', compact('settings'));
    }

    public function create()
    {
        return view('admin.settings.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateSetting($request);
        AppSetting::create($data);

        return redirect()->route('settings.index')->with('success', 'Setting created.');
    }

    public function edit(AppSetting $setting)
    {
        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request, AppSetting $setting)
    {
        $data = $this->validateSetting($request, $setting);
        $setting->update($data);

        return redirect()->route('settings.index')->with('success', 'Setting updated.');
    }

    public function destroy(AppSetting $setting)
    {
        $setting->delete();

        return redirect()->route('settings.index')->with('success', 'Setting deleted.');
    }

    public function sendTestEmail(Request $request)
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        $settings = $this->getActiveSettings();
        $this->applyRuntimeSmtpSettings($settings);

        // Keep test emails fully separate from registration success emails.
        $subject = $this->cleanEmailSubject($settings['test_email_subject'] ?? 'SMTP Delivery Test Email');
        $bodyTemplate = $settings['test_email_body'] ?? 'This is a test email from Settings. It is only for SMTP verification.';
        $body = strtr($bodyTemplate, [
            '{name}' => 'Test User',
            '{email}' => $validated['test_email'],
            '{phone}' => 'N/A',
            '{code}' => 'TEST',
        ]) . "\n\nSent at: " . now()->toDateTimeString();
        $logoUrlSetting = $settings['email_logo_url'] ?? null;

        try {
            Mail::send([], [], function ($message) use ($validated, $subject, $body, $logoUrlSetting) {
                $subject = $this->cleanEmailSubject($subject);
                $logoSrc = $this->resolveEmailLogoSrc($message, $logoUrlSetting);
                $html = $this->buildStyledEmailHtml($subject, $body, $logoSrc);
                $message->to($validated['test_email'])->subject($subject);
                $message->html($html);
            });
        } catch (\Throwable $exception) {
            return back()->with('error', 'Test email failed: ' . $exception->getMessage());
        }

        return back()->with('success', 'Test email sent successfully to ' . $validated['test_email'] . '.');
    }

    public function deliveryCheck()
    {
        $settings = $this->getActiveSettings();

        $host = trim((string) ($settings['smtp_host'] ?? ''));
        $port = (int) trim((string) ($settings['smtp_port'] ?? ''));
        $encryption = trim((string) ($settings['smtp_encryption'] ?? ''));

        if ($host === '' || $port <= 0) {
            return back()->with('error', 'Delivery check failed: SMTP host/port are not configured in Settings.');
        }

        $errorNumber = 0;
        $errorMessage = '';
        $connection = @fsockopen($host, $port, $errorNumber, $errorMessage, 5);

        if (! $connection) {
            return back()->with(
                'error',
                "Delivery check failed: unable to connect to {$host}:{$port}. {$errorMessage} ({$errorNumber})"
            );
        }

        fclose($connection);

        $securityText = $encryption !== '' ? " using {$encryption} encryption setting" : '';

        return back()->with('success', "Delivery check passed: SMTP server {$host}:{$port} is reachable{$securityText}.");
    }

    public function clearPlatformData()
    {
        $admin = Auth::guard('admin')->user();
        if (! $admin || $admin->role !== 'super_admin') {
            abort(403);
        }

        DB::transaction(function () {
            // Use DELETE (not TRUNCATE) so MySQL doesn't auto-commit and break transaction handling.
            DB::table('engagements')->delete();
            DB::table('surveys')->delete();
            DB::table('members')->delete();
        });

        return back()->with('success', 'Platform data cleared. Registrations, surveys, and engagements have been reset.');
    }

    protected function validateSetting(Request $request, ?AppSetting $existingSetting = null): array
    {
        $validated = $request->validate([
            'category' => ['required', 'in:email_content,smtp_connection,sms_connection'],
            'label' => ['required', 'string', 'max:255'],
            'setting_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('app_settings', 'setting_key')->ignore($existingSetting?->id),
            ],
            'setting_value' => ['nullable', 'string'],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $settingKey = Str::snake($validated['setting_key']);
        $settingValue = $validated['setting_value'] ?? null;

        if ($settingKey === 'email_logo_url' && $request->hasFile('logo_file')) {
            $uploadDir = public_path('uploads/email-logos');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploaded = $request->file('logo_file');
            $fileName = Str::uuid()->toString() . '.' . $uploaded->getClientOriginalExtension();
            $uploaded->move($uploadDir, $fileName);
            $settingValue = 'uploads/email-logos/' . $fileName;

            if ($existingSetting && str_starts_with((string) $existingSetting->setting_value, 'uploads/email-logos/')) {
                $oldPath = public_path((string) $existingSetting->setting_value);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        return [
            'category' => $validated['category'],
            'label' => $validated['label'],
            'setting_key' => $settingKey,
            'setting_value' => $settingValue,
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    protected function getActiveSettings(): array
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

    protected function cleanEmailSubject(string $subject): string
    {
        $cleaned = preg_replace('/\[\s*test\s*\]\s*/i', '', $subject);
        $cleaned = str_ireplace('[test]', '', (string) $cleaned);
        $cleaned = trim((string) $cleaned);

        return $cleaned !== '' ? $cleaned : 'Registration Confirmation';
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
}
