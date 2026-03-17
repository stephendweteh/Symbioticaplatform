<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
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

        $subject = '[Test] ' . ($settings['registration_email_subject'] ?? 'SMTP Test Email');
        $body = 'This is a test email from Settings. Sent at ' . now()->toDateTimeString();

        try {
            Mail::html('<p style="font-family:Arial,sans-serif;">' . e($body) . '</p>', function ($message) use ($validated, $subject) {
                $message->to($validated['test_email'])->subject($subject);
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

    protected function validateSetting(Request $request, ?AppSetting $existingSetting = null): array
    {
        $validated = $request->validate([
            'category' => ['required', 'in:email_content,smtp_connection'],
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
}
