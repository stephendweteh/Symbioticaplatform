@extends('layouts.app')

@section('title', 'Register')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl p-8 mx-4">
        <h1 class="text-2xl font-semibold text-slate-900 mb-6 text-center">Register</h1>

        <div class="mb-4">
            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center h-10 w-10 rounded-full border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
               title="Back to Home"
               aria-label="Back to Home">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M17 10a1 1 0 0 1-1 1H6.414l3.293 3.293a1 1 0 0 1-1.414 1.414l-5-5a1 1 0 0 1 0-1.414l5-5a1 1 0 0 1 1.414 1.414L6.414 9H16a1 1 0 0 1 1 1Z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
            @csrf

            @if(!empty($registrationFields) && $registrationFields->isNotEmpty())
                @foreach($registrationFields as $field)
                    @php
                        $inputName = $field->field_key;
                        $isGenderDefault = $field->field_key === 'gender' && empty($field->options);
                        $fieldOptions = $isGenderDefault ? ['male', 'female', 'other'] : ($field->options ?? []);
                    @endphp
                    <div>
                        @if($field->field_type !== 'consent')
                            <label class="block text-sm font-medium text-slate-700">
                                {{ $field->label }}
                                @if($field->is_required)
                                    <span class="text-red-600">*</span>
                                @endif
                            </label>
                        @endif

                        @if($field->field_type === 'consent')
                            @php
                                $consentText = $field->options[0] ?? '';
                            @endphp
                            @if($consentText !== '')
                                <textarea rows="4" readonly
                                          class="mt-1 block w-full rounded-md border-slate-200 bg-slate-50 text-sm text-slate-800 shadow-sm">
{{ $consentText }}
                                </textarea>
                            @endif
                            <label class="mt-2 inline-flex items-start gap-2">
                                <input type="checkbox" name="{{ $inputName }}" value="1"
                                       class="mt-1 rounded border-slate-300 text-violet-600 focus:ring-violet-500"
                                       {{ old($inputName) ? 'checked' : '' }}>
                                <span class="text-sm text-slate-700">
                                    {{ $field->label }}
                                    @if($field->is_required)
                                        <span class="text-red-600">*</span>
                                    @endif
                                </span>
                            </label>
                        @elseif($field->field_type === 'textarea')
                            <textarea name="{{ $inputName }}" rows="3"
                                      class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">{{ old($inputName) }}</textarea>
                        @elseif($field->field_type === 'select')
                            <select name="{{ $inputName }}"
                                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                                <option value="">Select</option>
                                @foreach($fieldOptions as $option)
                                    <option value="{{ $option }}" @selected(old($inputName) === $option)>{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $field->field_type }}" name="{{ $inputName }}" value="{{ old($inputName) }}"
                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                        @endif

                        @error($inputName)
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            @endif

            <div class="pt-4">
                <button type="submit"
                        class="inline-flex w-full justify-center rounded-xl border border-violet-500 bg-violet-500 text-white py-3 px-4 text-base font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
                    Submit Registration
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

