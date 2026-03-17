<div>
    <label class="block text-sm font-medium text-slate-700">Name</label>
    <input type="text" name="name" value="{{ old('name', $user?->name) }}"
           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
    @error('name')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Email</label>
    <input type="email" name="email" value="{{ old('email', $user?->email) }}"
           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
    @error('email')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Role</label>
    @php $selectedRole = old('role', $user?->role ?? 'admin'); @endphp
    <select name="role"
            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
        <option value="admin" @selected($selectedRole === 'admin')>Admin</option>
        <option value="super_admin" @selected($selectedRole === 'super_admin')>Super Admin</option>
    </select>
    @error('role')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">
            Password @if(isset($isEdit) && $isEdit) (optional) @endif
        </label>
        <input type="password" name="password"
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
        @error('password')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
        <input type="password" name="password_confirmation"
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
    </div>
</div>

<div class="pt-4 flex items-center justify-between">
    <a href="{{ route('users.index') }}" class="text-sm text-slate-600 hover:text-slate-800">
        Cancel
    </a>
    <button type="submit"
            class="inline-flex items-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600 transition">
        Save User
    </button>
</div>
