@extends('layouts.app')

@section('title', 'Survey Submissions')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-6xl mx-auto px-4">
        @include('admin.partials.topbar')

        <h1 class="text-3xl font-semibold text-slate-900 mb-6 text-center">Survey Submissions</h1>

        <div class="bg-white rounded-2xl shadow-lg p-6">
            @if(session('success'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <form method="GET" action="{{ route('admin.surveys.index') }}" class="flex flex-wrap items-end gap-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Sort By</label>
                        <select name="sort_by"
                                class="mt-1 rounded-md border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500">
                            @foreach($surveySortOptions as $value => $label)
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
                            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Apply
                    </button>
                </form>

                <div class="flex flex-wrap items-end gap-2">
                    <a href="{{ route('admin.export.surveys', ['format' => 'csv', 'sort_by' => $sortBy, 'sort_dir' => $sortDir]) }}"
                       class="inline-flex items-center rounded-lg border border-violet-300 bg-violet-50 px-3 py-2 text-sm font-medium text-violet-700 hover:bg-violet-100">
                        Export CSV
                    </a>
                    <a href="{{ route('admin.export.surveys', ['format' => 'xls', 'sort_by' => $sortBy, 'sort_dir' => $sortDir]) }}"
                       class="inline-flex items-center rounded-lg border border-violet-300 bg-violet-50 px-3 py-2 text-sm font-medium text-violet-700 hover:bg-violet-100">
                        Export XLS
                    </a>
                    <a href="{{ route('admin.export.surveys', ['format' => 'pdf', 'sort_by' => $sortBy, 'sort_dir' => $sortDir]) }}"
                       class="inline-flex items-center rounded-lg border border-violet-300 bg-violet-50 px-3 py-2 text-sm font-medium text-violet-700 hover:bg-violet-100">
                        Export PDF
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Member</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Code</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Submitted At</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Survey Entry</th>
                            @if($isSuperAdmin)
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($surveys as $survey)
                            @php
                                $legacyValues = [
                                    'question_1' => $survey->question_1,
                                    'question_2' => $survey->question_2,
                                    'question_3' => $survey->question_3,
                                    'question_4' => $survey->question_4,
                                    'question_5' => $survey->question_5,
                                    'comments' => $survey->comments,
                                ];
                                $knownFieldKeys = $surveyFields->pluck('field_key')->all();
                                $extraData = collect($survey->additional_data ?? [])->except($knownFieldKeys);
                            @endphp
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-slate-900">{{ $survey->member?->full_name ?? 'Unknown Member' }}</div>
                                    <div class="text-xs text-slate-500">{{ $survey->member?->email ?? 'No email' }}</div>
                                </td>
                                <td class="px-3 py-2 font-mono text-sm text-slate-700">
                                    {{ $survey->member?->unique_code ?? '----' }}
                                </td>
                                <td class="px-3 py-2 text-sm text-slate-700">
                                    {{ optional($survey->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-3 py-2">
                                    <details class="group">
                                        <summary class="cursor-pointer list-none inline-flex items-center rounded-lg border border-violet-300 bg-violet-50 px-3 py-1.5 text-xs font-medium text-violet-700 hover:bg-violet-100">
                                            View Survey
                                            <span class="ml-2 transition-transform group-open:rotate-180">&#9662;</span>
                                        </summary>

                                        <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach($surveyFields as $field)
                                                    @php
                                                        $value = array_key_exists($field->field_key, $legacyValues)
                                                            ? $legacyValues[$field->field_key]
                                                            : data_get($survey->additional_data, $field->field_key);
                                                    @endphp
                                                    @if(filled($value))
                                                        <div>
                                                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $field->label }}</dt>
                                                            <dd class="mt-1 text-sm text-slate-800 break-words">
                                                                {{ is_array($value) ? implode(', ', $value) : (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) }}
                                                            </dd>
                                                        </div>
                                                    @endif
                                                @endforeach

                                                @foreach($extraData as $key => $value)
                                                    @if(filled($value))
                                                        <div>
                                                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ \Illuminate\Support\Str::headline((string) $key) }}</dt>
                                                            <dd class="mt-1 text-sm text-slate-800 break-words">
                                                                {{ is_array($value) ? implode(', ', $value) : (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) }}
                                                            </dd>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </dl>

                                            @if($surveyFields->isEmpty() && empty($survey->additional_data))
                                                <p class="text-sm text-slate-500">No survey responses captured for this entry.</p>
                                            @endif
                                        </div>
                                    </details>
                                </td>
                                @if($isSuperAdmin)
                                    <td class="px-3 py-2">
                                        <form method="POST"
                                              action="{{ route('admin.surveys.destroy', $survey) }}"
                                              onsubmit="return confirm('Delete this survey submission? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-lg border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 5 : 4 }}" class="px-3 py-4 text-center text-sm text-slate-500">
                                    No survey submissions yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $surveys->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
