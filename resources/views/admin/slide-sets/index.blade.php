@extends('layouts.app')

@section('title', 'Experiences')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        @include('admin.partials.topbar')

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Experiences</h1>
            <a href="{{ route('slide-sets.create') }}"
               class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                Add Experience
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
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Title</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Thumbnail</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Slides</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Active</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($slideSets as $slideSet)
                            <tr>
                                <td class="px-3 py-2 text-slate-700">{{ $slideSet->order_number }}</td>
                                <td class="px-3 py-2">
                                    <p class="font-medium text-slate-900">{{ $slideSet->title }}</p>
                                    @if($slideSet->description)
                                        <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($slideSet->description, 80) }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if($slideSet->thumbnail_path)
                                        <img src="{{ asset($slideSet->thumbnail_path) }}" alt="{{ $slideSet->title }}"
                                             class="h-12 w-20 rounded-md border border-slate-200 object-cover">
                                    @else
                                        <span class="text-xs text-slate-400">No thumbnail</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-700">{{ $slideSet->slides_count }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $slideSet->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $slideSet->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('slide-sets.edit', $slideSet) }}"
                                       class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-1 px-3 text-xs font-medium hover:bg-slate-50">
                                        Edit
                                    </a>
                                    <form action="{{ route('slide-sets.destroy', $slideSet) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Delete this experience?')"
                                                class="inline-flex items-center rounded-lg border border-red-300 bg-red-50 text-red-700 py-1 px-3 text-xs font-medium hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-sm text-slate-500">
                                    No experiences yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $slideSets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

