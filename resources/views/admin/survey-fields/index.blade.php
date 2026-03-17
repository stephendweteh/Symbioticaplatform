@extends('layouts.app')

@section('title', 'Survey Fields')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        @include('admin.partials.topbar')

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Survey Fields</h1>
            <a href="{{ route('survey-fields.create') }}"
               class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                Add Field
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">All Survey Fields</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Order</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Label</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Key</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Type</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Required</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Active</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($fields as $field)
                            <tr>
                                <td class="px-3 py-2">{{ $field->sort_order }}</td>
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $field->label }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $field->field_key }}</td>
                                <td class="px-3 py-2">{{ $field->field_type }}</td>
                                <td class="px-3 py-2">{{ $field->is_required ? 'Yes' : 'No' }}</td>
                                <td class="px-3 py-2">{{ $field->is_active ? 'Yes' : 'No' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('survey-fields.edit', $field) }}"
                                       class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-1 px-3 text-xs font-medium hover:bg-slate-50">
                                        Edit
                                    </a>
                                    <form action="{{ route('survey-fields.destroy', $field) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Delete this survey field?')"
                                                class="inline-flex items-center rounded-lg border border-red-300 bg-red-50 text-red-700 py-1 px-3 text-xs font-medium hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-slate-500">No survey fields found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $fields->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
