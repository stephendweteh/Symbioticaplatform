<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exhibition System</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    @endif
</head>
<body class="min-h-screen flex items-center justify-center bg-[#efefef]">
    @php
        $resolvedLogoSrc = $appLogoSrc ?? null;
        if (!$resolvedLogoSrc) {
            $logoSetting = \App\Models\AppSetting::query()
                ->where('setting_key', 'app_logo_url')
                ->where('is_active', true)
                ->value('setting_value');

            if ($logoSetting && (str_starts_with($logoSetting, 'http://') || str_starts_with($logoSetting, 'https://'))) {
                $resolvedLogoSrc = $logoSetting;
            } elseif ($logoSetting) {
                $resolvedLogoSrc = asset(ltrim($logoSetting, '/'));
            } else {
                $resolvedLogoSrc = asset('logonew.png');
            }
        }
    @endphp
    <div class="max-w-5xl w-full px-6 py-10">
        <div class="text-center">
            <div class="flex justify-center mb-10">
                <img
                    src="{{ $resolvedLogoSrc }}"
                    alt="Symbiotica Logo"
                    class="h-[150px] w-auto"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                <a href="{{ route('register.index') }}"
                   class="h-40 w-full inline-flex flex-col items-center justify-center gap-4 rounded-3xl bg-[#0b6f95] text-white text-[40px] font-medium shadow-md hover:bg-[#095f7f] transition">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a8.25 8.25 0 0 1 15 0M19.5 8.25h3m-1.5-1.5v3"/>
                        </svg>
                    </span>
                    <span class="text-4xl leading-none">Register</span>
                </a>

                <a href="{{ route('engagement.index') }}"
                   class="h-40 w-full inline-flex flex-col items-center justify-center gap-4 rounded-3xl bg-[#0b6f95] text-white text-[40px] font-medium shadow-md hover:bg-[#095f7f] transition">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25v7.5m9-7.5v7.5M5.25 12h13.5M9.75 6h4.5a2.25 2.25 0 0 1 2.25 2.25v7.5A2.25 2.25 0 0 1 14.25 18h-4.5A2.25 2.25 0 0 1 7.5 15.75v-7.5A2.25 2.25 0 0 1 9.75 6Z"/>
                        </svg>
                    </span>
                    <span class="text-4xl leading-none">Engagement</span>
                </a>

                <a href="{{ route('survey.index') }}"
                   class="h-40 w-full inline-flex flex-col items-center justify-center gap-4 rounded-3xl bg-[#0b6f95] text-white text-[40px] font-medium shadow-md hover:bg-[#095f7f] transition">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M9 8h6M6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75A2.25 2.25 0 0 1 6.75 4.5Z"/>
                        </svg>
                    </span>
                    <span class="text-4xl leading-none">Survey</span>
                </a>

                <a href="{{ route('admin.index') }}"
                   class="h-40 w-full inline-flex flex-col items-center justify-center gap-4 rounded-3xl bg-[#0b6f95] text-white text-[40px] font-medium shadow-md hover:bg-[#095f7f] transition">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-1.5 0h12a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5H6a1.5 1.5 0 0 1-1.5-1.5V12A1.5 1.5 0 0 1 6 10.5Z"/>
                        </svg>
                    </span>
                    <span class="text-4xl leading-none">Admin</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

