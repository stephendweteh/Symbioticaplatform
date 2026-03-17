@extends('layouts.app')

@section('title', 'Engagement')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl p-8 mx-4">
        <h1 class="text-2xl font-semibold text-slate-900 mb-6 text-center">Engagement</h1>

        <div class="mb-4">
            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center h-10 w-10 rounded-full border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
               title="Back to Home"
               aria-label="Back to Home">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M17 10a1 1 0 0 1-1 1H6.414l3.293 3.293a1 1 0 0 1-1.414 1.414l-5-5a1 1 0 0 1 0-1.414l5-5a1 1 0 0 1 1.414 1.414L6.414 9H16a1 1 0 0 1 1 1Z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>

        <p class="text-slate-500 mb-4 text-center text-sm">
            Enter your 4-digit code to start or resume the Experience.
        </p>

        <form method="POST" action="{{ route('engagement.start') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">4-digit Code</label>
                <input type="text" name="code" value="{{ old('code') }}"
                       maxlength="4"
                       class="mt-1 tracking-[0.5em] text-center text-lg block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                @error('code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4">
                <button type="submit"
                        class="inline-flex w-full justify-center rounded-xl border border-violet-500 bg-violet-500 text-white py-3 px-4 text-base font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                    Start Engagement
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

