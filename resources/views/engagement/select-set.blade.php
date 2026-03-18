@extends('layouts.app')

@section('title', 'Select Experience')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-6xl w-full bg-white rounded-2xl shadow-xl p-8 mx-4">
        <div class="flex items-start justify-between gap-4 mb-6">
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
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($slideSets as $slideSet)
                    <a href="{{ route('engagement.subcategories', ['member' => $member->id, 'slideSet' => $slideSet->id]) }}"
                       class="group rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md hover:border-violet-300 transition overflow-hidden">
                        <div class="h-40 bg-slate-100 flex items-center justify-center overflow-hidden">
                            @if($slideSet->thumbnail_path)
                                <img src="{{ asset($slideSet->thumbnail_path) }}" alt="{{ $slideSet->title }}"
                                     class="w-full h-full object-cover group-hover:scale-[1.02] transition">
                            @else
                                <div class="text-slate-400 text-sm">No thumbnail</div>
                            @endif
                        </div>
                        <div class="p-4">
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

