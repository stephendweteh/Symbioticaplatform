@extends('layouts.app')

@section('title', 'Admin Login')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 mx-4">
        <h1 class="text-2xl font-semibold text-slate-900 mb-6 text-center">Admin Login</h1>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Password</label>
                <input type="password" name="password"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                    <span class="text-slate-700">Remember me</span>
                </label>
            </div>

            <div class="pt-4">
                <button type="submit"
                        class="inline-flex w-full justify-center rounded-xl border border-violet-500 bg-violet-500 text-white py-3 px-4 text-base font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                    Log in
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

