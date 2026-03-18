@extends('layouts.app')

@section('title', 'Slides')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        @include('admin.partials.topbar')

        <div class="mb-4">
            <a href="{{ route('admin.index') }}"
               class="inline-flex items-center justify-center h-10 w-10 rounded-full border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
               title="Back to Dashboard"
               aria-label="Back to Dashboard">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M17 10a1 1 0 0 1-1 1H6.414l3.293 3.293a1 1 0 0 1-1.414 1.414l-5-5a1 1 0 0 1 0-1.414l5-5a1 1 0 0 1 1.414 1.414L6.414 9H16a1 1 0 0 1 1 1Z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Slides</h1>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('slide-sets.index') }}"
                   class="inline-flex items-center rounded-xl border border-slate-300 bg-white text-slate-700 py-2 px-4 text-sm font-medium shadow-sm hover:bg-slate-50 transition">
                    View Experiences
                </a>
                <a href="{{ route('slide-subcategories.index') }}"
                   class="inline-flex items-center rounded-xl border border-slate-300 bg-white text-slate-700 py-2 px-4 text-sm font-medium shadow-sm hover:bg-slate-50 transition">
                    View Sub Categories
                </a>
                <a href="{{ route('slides.create') }}"
                   class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                    Add Slide
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Order</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Experience</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Sub Category</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Title</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Preview</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Image Path</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Active</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($slides as $slide)
                            <tr>
                                <td class="px-3 py-2 text-sm text-slate-700">{{ $slide->order_number }}</td>
                                <td class="px-3 py-2 text-sm text-slate-700">{{ $slide->slideSubcategory?->slideSet?->title ?? 'Unassigned' }}</td>
                                <td class="px-3 py-2 text-sm text-slate-700">{{ $slide->slideSubcategory?->title ?? 'Unassigned' }}</td>
                                <td class="px-3 py-2 text-sm text-slate-900">{{ $slide->title }}</td>
                                <td class="px-3 py-2">
                                    @if($slide->image_path)
                                        <img src="{{ asset($slide->image_path) }}" alt="{{ $slide->title }}"
                                             class="h-12 w-20 rounded-md border border-slate-200 object-cover">
                                    @else
                                        <span class="text-xs text-slate-400">No image</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-500">{{ $slide->image_path }}</td>
                                <td class="px-3 py-2 text-sm">
                                    @if($slide->is_active)
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right text-sm">
                                    <a href="{{ route('slides.edit', $slide) }}"
                                       class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-1 px-3 text-xs font-medium hover:bg-slate-50">
                                        Edit
                                    </a>
                                    <form action="{{ route('slides.destroy', $slide) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Delete this slide?')"
                                                class="inline-flex items-center rounded-lg border border-red-300 bg-red-50 text-red-700 py-1 px-3 text-xs font-medium hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-4 text-center text-sm text-slate-500">
                                    No slides yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $slides->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

