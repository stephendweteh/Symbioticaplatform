@extends('layouts.app')

@section('title', 'Settings')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-6xl mx-auto px-4">
        @include('admin.partials.topbar')

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Settings</h1>
            <a href="{{ route('settings.create') }}"
               class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                Add Setting
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Email Test & Delivery Check</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <form method="POST" action="{{ route('settings.test-email') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Test Email Address</label>
                        <input type="email" name="test_email" value="{{ old('test_email') }}" placeholder="name@example.com"
                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                        @error('test_email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                        Send Test Email
                    </button>
                </form>

                <form method="POST" action="{{ route('settings.delivery-check') }}" class="space-y-3">
                    @csrf
                    <p class="text-sm text-slate-600">
                        Check whether SMTP server host and port from Settings are reachable.
                    </p>
                    <button type="submit"
                            class="inline-flex items-center rounded-xl border border-slate-300 bg-white text-slate-700 py-2 px-4 text-sm font-medium shadow-sm hover:bg-slate-50 transition">
                        Run Delivery Check
                    </button>
                </form>
            </div>
        </div>

        @if(auth('admin')->user()?->role === 'super_admin')
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 border border-red-200">
                <h2 class="text-lg font-semibold text-red-700 mb-2">Reset Platform Data</h2>
                <p class="text-sm text-slate-600 mb-4">
                    Clear all registrations, survey submissions, and engagement progress to start fresh.
                    Email/SMS settings and other configurations will remain unchanged.
                </p>
                <form method="POST" action="{{ route('settings.clear-platform-data') }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('This will permanently clear registrations, surveys, and engagements. Continue?')"
                            class="inline-flex items-center rounded-xl border border-red-300 bg-red-50 text-red-700 py-2 px-4 text-sm font-medium shadow-sm hover:bg-red-100 transition">
                        Clear Registrations & Survey Data
                    </button>
                </form>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">App Branding</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Label</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Value</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Active</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse(($settings['app_branding'] ?? collect()) as $setting)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-slate-900">{{ $setting->label }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ \Illuminate\Support\Str::limit($setting->setting_value, 80) }}</td>
                                    <td class="px-3 py-2">{{ $setting->is_active ? 'Yes' : 'No' }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('settings.edit', $setting) }}"
                                           class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-1 px-3 text-xs font-medium hover:bg-slate-50">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-slate-500">No app branding settings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Email Content</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Label</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Value</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Active</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse(($settings['email_content'] ?? collect()) as $setting)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-slate-900">{{ $setting->label }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ \Illuminate\Support\Str::limit($setting->setting_value, 80) }}</td>
                                    <td class="px-3 py-2">{{ $setting->is_active ? 'Yes' : 'No' }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('settings.edit', $setting) }}"
                                           class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-1 px-3 text-xs font-medium hover:bg-slate-50">
                                            Edit
                                        </a>
                                        <form action="{{ route('settings.destroy', $setting) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Delete this setting?')"
                                                    class="inline-flex items-center rounded-lg border border-red-300 bg-red-50 text-red-700 py-1 px-3 text-xs font-medium hover:bg-red-100">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-slate-500">No email content settings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">SMTP Connection Details</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Label</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Value</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Active</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse(($settings['smtp_connection'] ?? collect()) as $setting)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-slate-900">{{ $setting->label }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ \Illuminate\Support\Str::limit($setting->setting_value, 80) }}</td>
                                    <td class="px-3 py-2">{{ $setting->is_active ? 'Yes' : 'No' }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('settings.edit', $setting) }}"
                                           class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-1 px-3 text-xs font-medium hover:bg-slate-50">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-slate-500">No SMTP settings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">SMS Connection Details (Arkesel)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Label</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Value</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Active</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse(($settings['sms_connection'] ?? collect()) as $setting)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-slate-900">{{ $setting->label }}</td>
                                    <td class="px-3 py-2 text-slate-600">{{ \Illuminate\Support\Str::limit($setting->setting_value, 80) }}</td>
                                    <td class="px-3 py-2">{{ $setting->is_active ? 'Yes' : 'No' }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('settings.edit', $setting) }}"
                                           class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-1 px-3 text-xs font-medium hover:bg-slate-50">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-slate-500">No SMS settings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
