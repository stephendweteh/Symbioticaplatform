@extends('layouts.app')

@section('title', 'Sub Categories')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-6xl mx-auto px-4">
        @include('admin.partials.topbar')

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Sub Categories</h1>
            <a href="{{ route('slide-subcategories.create') }}"
               class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                Add Sub Category
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

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Order</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Experience</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Sub Category</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Thumbnail</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Slides</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Active</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($subcategories as $subcategory)
                            <tr>
                                <td class="px-3 py-2 text-slate-700">{{ $subcategory->order_number }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $subcategory->slideSet?->title }}</td>
                                <td class="px-3 py-2">
                                    <p class="font-medium text-slate-900">{{ $subcategory->title }}</p>
                                    @if($subcategory->description)
                                        <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($subcategory->description, 80) }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if($subcategory->thumbnail_path)
                                        <img src="{{ asset($subcategory->thumbnail_path) }}" alt="{{ $subcategory->title }}"
                                             class="h-12 w-20 rounded-md border border-slate-200 object-cover">
                                    @else
                                        <span class="text-xs text-slate-400">No thumbnail</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-700">{{ $subcategory->slides_count }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $subcategory->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('slide-subcategories.edit', $subcategory) }}"
                                       class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-1 px-3 text-xs font-medium hover:bg-slate-50">
                                        Edit
                                    </a>
                                    <form action="{{ route('slide-subcategories.destroy', $subcategory) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Delete this sub category?')"
                                                class="inline-flex items-center rounded-lg border border-red-300 bg-red-50 text-red-700 py-1 px-3 text-xs font-medium hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-sm text-slate-500">
                                    No sub categories yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $subcategories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

