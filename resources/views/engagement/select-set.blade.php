@extends('layouts.app')

@section('title', 'Select Experience')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6">
    <div class="w-full max-w-6xl rounded-2xl bg-white p-5 shadow-xl sm:p-7 lg:p-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Choose an Experience</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $member->full_name }} (Code: <span class="font-mono">{{ $member->unique_code }}</span>)
                </p>
            </div>
            <a href="{{ route('engagement.index') }}"
               class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                Change Code
            </a>
        </div>

        @error('slide_set')
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ $message }}
            </div>
        @enderror

        @if($slideSets->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-slate-600 text-center">
                No active experiences are available yet. Please contact an admin.
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($slideSets as $slideSet)
                    <a href="{{ route('engagement.subcategories', ['member' => $member->id, 'slideSet' => $slideSet->id]) }}"
                       class="group flex h-full w-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-md">
                        <div class="h-44 bg-slate-100 flex items-center justify-center overflow-hidden">
                            @if($slideSet->thumbnail_path)
                                <img src="{{ asset($slideSet->thumbnail_path) }}" alt="{{ $slideSet->title }}"
                                     class="w-full h-full object-cover group-hover:scale-[1.02] transition">
                            @else
                                <div class="text-slate-400 text-sm">No thumbnail</div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <h2 class="text-base font-semibold text-slate-900">{{ $slideSet->title }}</h2>
                            @if($slideSet->description)
                                <p class="text-sm text-slate-600 mt-3">{{ \Illuminate\Support\Str::limit($slideSet->description, 110) }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

