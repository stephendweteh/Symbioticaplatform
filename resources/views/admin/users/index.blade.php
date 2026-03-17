@extends('layouts.app')

@section('title', 'Admin Users')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        @include('admin.partials.topbar')

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Users</h1>
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                Add Admin User
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('user'))
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ $errors->first('user') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Name</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Email</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Role</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Created</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-3 py-2">{{ $user->id }}</td>
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $user->name }}</td>
                                <td class="px-3 py-2">{{ $user->email }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $user->role === 'super_admin' ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $user->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">{{ $user->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-700 py-1 px-3 text-xs font-medium hover:bg-slate-50">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Delete this admin user?')"
                                                class="inline-flex items-center rounded-lg border border-red-300 bg-red-50 text-red-700 py-1 px-3 text-xs font-medium hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-slate-500">No admin users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
