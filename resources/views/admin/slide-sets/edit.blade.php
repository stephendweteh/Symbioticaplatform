@extends('layouts.app')

@section('title', 'Edit Experience')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-3xl mx-auto px-4">
        @include('admin.partials.topbar')

        <h1 class="text-2xl font-semibold text-slate-900 mb-6">Edit Experience</h1>

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <form method="POST" action="{{ route('slide-sets.update', $slideSet) }}" class="space-y-4" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700">Set Title</label>
                    <input type="text" name="title" value="{{ old('title', $slideSet->title) }}"
                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                    @error('title')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Set Description</label>
                    <textarea name="description" rows="3"
                              class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">{{ old('description', $slideSet->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Thumbnail (optional replace)</label>
                    @if($slideSet->thumbnail_path)
                        <img src="{{ asset($slideSet->thumbnail_path) }}" alt="{{ $slideSet->title }}"
                             class="mt-2 mb-2 h-24 rounded-lg border border-slate-200 object-cover">
                    @endif
                    <input type="file" name="thumbnail_file" accept=".jpg,.jpeg,.png,.webp,.gif"
                           class="mt-1 block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-violet-100 file:text-violet-700 hover:file:bg-violet-200">
                    @error('thumbnail_file')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Order Number</label>
                        <input type="number" name="order_number" value="{{ old('order_number', $slideSet->order_number) }}"
                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                        @error('order_number')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2 mt-6">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $slideSet->is_active) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                        <span class="text-sm text-slate-700">Active</span>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-between">
                    <a href="{{ route('slide-sets.index') }}"
                       class="text-sm text-slate-600 hover:text-slate-800">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                        Update Experience
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

