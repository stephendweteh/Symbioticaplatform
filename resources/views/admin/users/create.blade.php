@extends('layouts.app')

@section('title', 'Add Admin User')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-3xl mx-auto px-4">
        @include('admin.partials.topbar')

        <h1 class="text-2xl font-semibold text-slate-900 mb-6">Add Admin User</h1>

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                @csrf
                @include('admin.users.form', ['user' => null, 'isEdit' => false])
            </form>
        </div>
    </div>
</div>
@endsection
