@extends('layouts.app')

@section('title', 'Admin Profile')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-3xl mx-auto px-4">
        @include('admin.partials.topbar')

        <h1 class="text-2xl font-semibold text-slate-900 mb-6">User Profile</h1>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Update Profile</h2>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Profile Picture</label>
                    <div class="flex items-center gap-4">
                        @if($admin->profile_picture)
                            <img src="{{ asset($admin->profile_picture) }}"
                                 alt="Profile picture"
                                 class="h-16 w-16 rounded-full object-cover border border-slate-300">
                        @else
                            <div class="h-16 w-16 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-semibold">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                        @endif
                        <input type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp"
                               class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-violet-100 file:text-violet-700 hover:file:bg-violet-200">
                    </div>
                    @error('profile_picture')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}"
                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}"
                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">New Password (optional)</label>
                        <input type="password" name="password"
                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                            class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border border-red-200">
            <h2 class="text-lg font-semibold text-red-700 mb-2">Delete Profile</h2>
            <p class="text-sm text-slate-600 mb-4">
                This will permanently delete your admin user. Type your email to confirm.
            </p>

            <form method="POST" action="{{ route('admin.profile.destroy') }}" class="space-y-3">
                @csrf
                @method('DELETE')

                <div>
                    <label class="block text-sm font-medium text-slate-700">Confirm Email</label>
                    <input type="email" name="confirm_email" placeholder="{{ $admin->email }}"
                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('confirm_email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        onclick="return confirm('Are you sure you want to delete your profile? This cannot be undone.')"
                        class="inline-flex items-center rounded-xl border border-red-300 bg-red-50 text-red-700 py-2 px-4 text-sm font-medium shadow-sm hover:bg-red-100 transition">
                    Delete Profile
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

