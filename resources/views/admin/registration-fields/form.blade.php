<div>
    <label class="block text-sm font-medium text-slate-700">Label</label>
    <input type="text" name="label" value="{{ old('label', $field?->label) }}"
           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
    @error('label')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Field Key (snake_case)</label>
    <input type="text" name="field_key" value="{{ old('field_key', $field?->field_key) }}"
           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
    @error('field_key')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">Field Type</label>
        <select name="field_type"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
            @php $selectedType = old('field_type', $field?->field_type ?? 'text'); @endphp
            @foreach(['text','email','tel','number','date','textarea','select'] as $type)
                <option value="{{ $type }}" @selected($selectedType === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        @error('field_type')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $field?->sort_order ?? 0) }}"
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
        @error('sort_order')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Options (one per line, for Select type)</label>
    <textarea name="options_input" rows="4"
              class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">{{ old('options_input', isset($field) && $field?->options ? implode("\n", $field->options) : '') }}</textarea>
    @error('options_input')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="flex items-center gap-6">
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_required" value="1" {{ old('is_required', $field?->is_required ?? false) ? 'checked' : '' }}
               class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
        <span class="text-sm text-slate-700">Required</span>
    </label>
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $field?->is_active ?? true) ? 'checked' : '' }}
               class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
        <span class="text-sm text-slate-700">Active</span>
    </label>
</div>

<div class="pt-4 flex items-center justify-between">
    <a href="{{ route('registration-fields.index') }}" class="text-sm text-slate-600 hover:text-slate-800">
        Cancel
    </a>
    <button type="submit"
            class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
        Save Field
    </button>
</div>

