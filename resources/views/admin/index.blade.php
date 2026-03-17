@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-6xl mx-auto px-4">
        <div class="no-print">
            @include('admin.partials.topbar')
        </div>

        <h1 class="text-3xl font-semibold text-slate-900 mb-6 text-center no-print">Admin Dashboard</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 no-print">
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Registrations</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['total_registrations'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Completed Engagements (5 stars)</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ $stats['engagement_completed'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Partially Done</p>
                <p class="mt-2 text-2xl font-semibold text-amber-600">{{ $stats['engagement_partial'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Pending Engagements</p>
                <p class="mt-2 text-2xl font-semibold text-slate-700">{{ $stats['engagement_pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Survey Submissions</p>
                <p class="mt-2 text-2xl font-semibold text-violet-600">{{ $stats['survey_submissions'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 print-table-only">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Registered Members</h2>

                <div class="flex flex-col sm:flex-row gap-2 no-print">
                    <form method="GET" action="{{ route('admin.index') }}" class="flex flex-wrap items-end gap-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Sort By</label>
                            <select name="sort_by"
                                    class="mt-1 rounded-md border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500">
                                @foreach($sortOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($sortBy === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Direction</label>
                            <select name="sort_dir"
                                    class="mt-1 rounded-md border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500">
                                <option value="asc" @selected($sortDir === 'asc')>Ascending</option>
                                <option value="desc" @selected($sortDir === 'desc')>Descending</option>
                            </select>
                        </div>
                        <button type="submit"
                                class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                            Apply
                        </button>
                    </form>

                    <div class="flex items-end gap-2">
                        <button type="button"
                                onclick="window.print()"
                                class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-2 px-3 text-sm font-medium hover:bg-slate-50">
                            Print
                        </button>
                        <a href="{{ route('admin.export.registrations', ['format' => 'csv', 'sort_by' => $sortBy, 'sort_dir' => $sortDir]) }}"
                           class="inline-flex items-center rounded-lg border border-violet-300 bg-violet-50 text-violet-700 py-2 px-3 text-sm font-medium hover:bg-violet-100">
                            Export CSV
                        </a>
                        <a href="{{ route('admin.export.registrations', ['format' => 'xls', 'sort_by' => $sortBy, 'sort_dir' => $sortDir]) }}"
                           class="inline-flex items-center rounded-lg border border-violet-300 bg-violet-50 text-violet-700 py-2 px-3 text-sm font-medium hover:bg-violet-100">
                            Export XLS
                        </a>
                        <a href="{{ route('admin.export.registrations', ['format' => 'pdf', 'sort_by' => $sortBy, 'sort_dir' => $sortDir]) }}"
                           class="inline-flex items-center rounded-lg border border-violet-300 bg-violet-50 text-violet-700 py-2 px-3 text-sm font-medium hover:bg-violet-100">
                            Export PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Name</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Code</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Contact</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Place of Practice (Institution)</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Engagement Status</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Survey</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($members as $member)
                            @php
                                $engagement = $member->engagements->first();
                                $statusLabel = 'Pending';
                                $statusClass = 'text-slate-600';

                                if ($engagement) {
                                    if ($engagement->status === 'completed') {
                                        $statusLabel = 'Completed (5 stars)';
                                        $statusClass = 'text-emerald-600';
                                    } elseif ($engagement->status === 'partial') {
                                        $statusLabel = 'Partially Done';
                                        $statusClass = 'text-amber-600';
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-slate-900">{{ $member->full_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $member->gender }}</div>
                                </td>
                                <td class="px-3 py-2 font-mono text-sm">
                                    {{ $member->unique_code }}
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-slate-800 text-sm">{{ $member->email }}</div>
                                    <div class="text-xs text-slate-500">{{ $member->phone }}</div>
                                </td>
                                <td class="px-3 py-2 text-sm text-slate-700">
                                    <div>{{ $member->organization }}</div>
                                    <div class="text-xs text-slate-500">{{ $member->role }}</div>
                                </td>
                                <td class="px-3 py-2 text-sm">
                                    <div class="{{ $statusClass }}">{{ $statusLabel }}</div>
                                    @if($engagement)
                                        <div class="text-xs text-slate-500">
                                            {{ $engagement->completion_percentage }}% •
                                            {{ $engagement->slides_viewed }}/{{ $engagement->total_slides }} slides
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm">
                                    @if($member->surveys->isNotEmpty())
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
                                            Submitted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                            Not submitted
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-sm text-slate-500">
                                    No registrations yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $members->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
@media print {
    .no-print {
        display: none !important;
    }

    body {
        background: #fff !important;
    }

    .print-table-only {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .print-table-only table {
        width: 100% !important;
        font-size: 11px !important;
    }
}
</style>
@endpush

