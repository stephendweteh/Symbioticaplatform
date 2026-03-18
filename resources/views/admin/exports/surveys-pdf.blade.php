<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Survey Submissions</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        h1 { margin: 0 0 8px; font-size: 18px; }
        .meta { margin-bottom: 12px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: top; text-align: left; }
        th { background: #f1f5f9; font-weight: 700; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <h1>Survey Submissions</h1>
    <p class="meta">
        Generated: {{ $generatedAt }} |
        Sort: {{ $sortBy }} ({{ strtoupper($sortDir) }}) |
        Total: {{ $rows->count() }}
    </p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Member</th>
                <th>Code</th>
                <th>Email</th>
                <th>Submitted At</th>
                <th>Responses</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $survey)
                @php
                    $responses = array_filter([
                        'Q1' => $survey->question_1,
                        'Q2' => $survey->question_2,
                        'Q3' => $survey->question_3,
                        'Q4' => $survey->question_4,
                        'Q5' => $survey->question_5,
                        'Comments' => $survey->comments,
                    ], fn ($value) => filled($value));
                    $extra = $survey->additional_data ?? [];
                @endphp
                <tr>
                    <td>{{ $survey->id }}</td>
                    <td>{{ optional($survey->member)->full_name ?? 'Unknown' }}</td>
                    <td>{{ optional($survey->member)->unique_code ?? '----' }}</td>
                    <td>{{ optional($survey->member)->email ?? '—' }}</td>
                    <td>{{ (string) $survey->created_at }}</td>
                    <td>
                        @if(empty($responses) && empty($extra))
                            <span class="muted">No data</span>
                        @else
                            @foreach($responses as $label => $value)
                                <div><strong>{{ $label }}:</strong> {{ $value }}</div>
                            @endforeach
                            @foreach($extra as $key => $value)
                                <div><strong>{{ \Illuminate\Support\Str::headline((string) $key) }}:</strong> {{ is_array($value) ? implode(', ', $value) : $value }}</div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">No survey submissions.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
