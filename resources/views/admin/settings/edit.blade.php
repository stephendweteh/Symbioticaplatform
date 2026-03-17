@extends('layouts.app')

@section('title', 'Edit Setting')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen">
    <div class="max-w-3xl mx-auto px-4">
        @include('admin.partials.topbar')

        <h1 class="text-2xl font-semibold text-slate-900 mb-6">Edit Setting</h1>

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <form method="POST" action="{{ route('settings.update', $setting) }}" class="space-y-4" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.settings.form', ['setting' => $setting])
            </form>
        </div>
    </div>
</div>
@endsection
