<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Registered Members Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        .meta { margin: 0 0 12px; color: #4b5563; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; }
    </style>
</head>
<body>
    <h1>Registered Members</h1>
    <p class="meta">
        Sorted by: {{ $sortBy }} ({{ $sortDir }}) |
        Generated: {{ $generatedAt }}
    </p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Place of Practice (Institution)</th>
                <th>Role</th>
                <th>Code</th>
                <th>Registered At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $member)
                <tr>
                    <td>{{ $member->id }}</td>
                    <td>{{ $member->full_name }}</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->phone }}</td>
                    <td>{{ $member->gender }}</td>
                    <td>{{ $member->organization }}</td>
                    <td>{{ $member->role }}</td>
                    <td>{{ $member->unique_code }}</td>
                    <td>{{ $member->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No registrations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
