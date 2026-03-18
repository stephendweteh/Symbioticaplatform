@php
    $adminUser = auth('admin')->user();
    $isSuperAdmin = $adminUser?->role === 'super_admin';
@endphp

<div class="mb-6 bg-white/90 backdrop-blur rounded-xl shadow p-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            @if($adminUser?->profile_picture)
                <img src="{{ asset($adminUser->profile_picture) }}"
                     alt="Admin profile"
                     class="h-12 w-12 rounded-full object-cover border border-slate-300">
            @else
                <div class="h-12 w-12 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-semibold">
                    {{ strtoupper(substr($adminUser->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <p class="text-sm text-slate-500">Signed in as</p>
                <p class="font-semibold text-slate-900">
                    {{ $adminUser->name }} ({{ $adminUser->email }})
                    <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $isSuperAdmin ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ $isSuperAdmin ? 'Super Admin' : 'Admin' }}
                    </span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.index') }}"
               class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                Dashboard
            </a>
            @if($isSuperAdmin)
                <a href="{{ route('slide-sets.index') }}"
                   class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                    Experiences
                </a>
                <a href="{{ route('slide-subcategories.index') }}"
                   class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                    Sub Categories
                </a>
                <a href="{{ route('slides.index') }}"
                   class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                    Slides
                </a>
                <a href="{{ route('registration-fields.index') }}"
                   class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                    Registration Fields
                </a>
                <a href="{{ route('survey-fields.index') }}"
                   class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                    Survey Fields
                </a>
                <a href="{{ route('settings.index') }}"
                   class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                    Settings
                </a>
                <a href="{{ route('users.index') }}"
                   class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                    Users
                </a>
            @endif
            <a href="{{ route('admin.profile') }}"
               class="inline-flex items-center rounded-lg border border-violet-500 bg-violet-500 text-white py-2 px-3 text-sm font-medium hover:bg-violet-600">
                Profile
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center rounded-lg border border-red-300 bg-red-50 text-red-700 py-2 px-3 text-sm font-medium hover:bg-red-100">
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>

