<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">Category</label>
        @php $selectedCategory = old('category', $setting?->category ?? 'email_content'); @endphp
        <select name="category"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
            <option value="email_content" @selected($selectedCategory === 'email_content')>Email Content</option>
            <option value="smtp_connection" @selected($selectedCategory === 'smtp_connection')>SMTP Connection</option>
            <option value="sms_connection" @selected($selectedCategory === 'sms_connection')>SMS Connection</option>
        </select>
        @error('category')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $setting?->sort_order ?? 0) }}"
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
        @error('sort_order')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Label</label>
    <input type="text" name="label" value="{{ old('label', $setting?->label) }}"
           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
    @error('label')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Setting Key (snake_case)</label>
    <input type="text" name="setting_key" value="{{ old('setting_key', $setting?->setting_key) }}"
           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
    @error('setting_key')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

@php
    $currentKey = old('setting_key', $setting?->setting_key);
    $isLogoSetting = $currentKey === 'email_logo_url';
@endphp

<div>
    @if($isLogoSetting)
        <label class="block text-sm font-medium text-slate-700">Upload Logo</label>
        @if(!empty($setting?->setting_value))
            <div class="mt-2 mb-3">
                <img src="{{ str_starts_with($setting->setting_value, 'http://') || str_starts_with($setting->setting_value, 'https://') ? $setting->setting_value : asset($setting->setting_value) }}"
                     alt="Current email logo"
                     class="h-16 w-auto rounded border border-slate-200 bg-white p-1">
            </div>
        @endif
        <input type="file" name="logo_file" accept=".jpg,.jpeg,.png,.webp,.gif"
               class="mt-1 block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-violet-100 file:text-violet-700 hover:file:bg-violet-200">
        <input type="hidden" name="setting_value" value="{{ old('setting_value', $setting?->setting_value) }}">
        <p class="mt-1 text-xs text-slate-500">Upload a logo image (max 5MB). This will be used in registration emails.</p>
        @error('logo_file')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    @else
        <label class="block text-sm font-medium text-slate-700">Value</label>
        <textarea name="setting_value" rows="6"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">{{ old('setting_value', $setting?->setting_value) }}</textarea>
        @error('setting_value')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>

<div class="flex items-center gap-6">
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $setting?->is_active ?? true) ? 'checked' : '' }}
               class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
        <span class="text-sm text-slate-700">Active</span>
    </label>
</div>

<div class="pt-4 flex items-center justify-between">
    <a href="{{ route('settings.index') }}" class="text-sm text-slate-600 hover:text-slate-800">
        Cancel
    </a>
    <button type="submit"
            class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
        Save Setting
    </button>
</div>
