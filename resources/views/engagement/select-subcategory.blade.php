@extends('layouts.app')

@section('title', 'Select Sub Category')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6">
    <div class="w-full max-w-6xl rounded-2xl bg-white p-5 shadow-xl sm:p-7 lg:p-8">
        <div class="mb-4">
            <a href="{{ route('engagement.sets', $member) }}"
               class="inline-flex items-center justify-center h-10 w-10 rounded-full border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
               title="Back to Experiences"
               aria-label="Back to Experiences">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M17 10a1 1 0 0 1-1 1H6.414l3.293 3.293a1 1 0 0 1-1.414 1.414l-5-5a1 1 0 0 1 0-1.414l5-5a1 1 0 0 1 1.414 1.414L6.414 9H16a1 1 0 0 1 1 1Z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>

        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-extrabold leading-tight text-violet-700">{{ $slideSet->title }}</h2>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $member->full_name }} (Code: <span class="font-mono">{{ $member->unique_code }}</span>)
                </p>
            </div>
        </div>

        @error('slide_set')
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ $message }}
            </div>
        @enderror

        @if($subcategories->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-slate-600 text-center">
                No active sub categories are available for this experience.
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($subcategories as $subcategory)
                    <a href="{{ route('engagement.start-subcategory', ['member' => $member->id, 'slideSet' => $slideSet->id, 'slideSubcategory' => $subcategory->id]) }}"
                       class="group flex h-full w-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-md">
                        <div class="h-44 bg-slate-100 flex items-center justify-center overflow-hidden">
                            @if($subcategory->thumbnail_path)
                                <img src="{{ asset($subcategory->thumbnail_path) }}" alt="{{ $subcategory->title }}"
                                     class="w-full h-full object-cover group-hover:scale-[1.02] transition">
                            @else
                                <div class="text-slate-400 text-sm">No thumbnail</div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <h2 class="text-2xl font-extrabold leading-tight text-slate-900">{{ $subcategory->title }}</h2>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $subcategory->active_slides_count }} slide(s)
                            </p>
                            @if($subcategory->description)
                                <p class="text-sm text-slate-600 mt-3">{{ \Illuminate\Support\Str::limit($subcategory->description, 110) }}</p>
                            @endif
                            <div class="mt-auto pt-4 inline-flex items-center text-sm font-medium text-violet-700">
                                View slides
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

