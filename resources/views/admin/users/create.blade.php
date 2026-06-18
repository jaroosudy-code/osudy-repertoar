@extends('layouts.app')
@section('title', 'Nový používateľ')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-600 text-sm">← Späť na používateľov</a>
</div>
<h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6">Nový používateľ</h1>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Meno <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" autocomplete="off"
                       class="w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('name') border-red-400 @enderror"
                       placeholder="napr. Martin">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" inputmode="email" autocomplete="off"
                       class="w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('email') border-red-400 @enderror"
                       placeholder="martin@skupinaosudy.sk">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Rola <span class="text-red-500">*</span></label>
                <select name="role_id"
                        class="w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('role_id') border-red-400 @enderror">
                    <option value="">– Vyber rolu –</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Heslo <span class="text-red-500">*</span></label>
            <div class="flex items-center border border-slate-300 dark:border-slate-600 rounded-lg focus-within:ring-2 focus-within:ring-amber-400 @error('password') border-red-400 @enderror">
                <input type="password" name="password" id="pw-admin" autocomplete="new-password"
                       class="flex-1 px-3 py-2.5 text-sm bg-transparent dark:text-slate-100 outline-none rounded-l-lg"
                       placeholder="min. 6 znakov">
                <button type="button" onclick="togglePw('pw-admin', this)" tabindex="-1"
                        class="px-2.5 text-slate-400 hover:text-slate-600 outline-none focus:outline-none transition-colors shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path class="eye-open" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                        <circle class="eye-open" cx="12" cy="12" r="3"/>
                        <path class="eye-shut" style="display:none" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                        <line class="eye-shut" style="display:none" x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>
            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Priradenie do kapely --}}
        <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Priradiť do kapely</p>
            <div class="space-y-3">
                @foreach($bands as $band)
                <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4" x-data="{ checked: {{ old('band_ids') && in_array($band->id, (array)old('band_ids')) ? 'true' : 'false' }} }">
                    <label class="flex items-center gap-3 cursor-pointer mb-2">
                        <input type="checkbox" name="band_ids[]" value="{{ $band->id }}"
                               x-model="checked"
                               class="w-4 h-4 accent-amber-500">
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $band->name }}</span>
                    </label>
                    <div x-show="checked" class="ml-7 mt-2 space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="band_admin[{{ $band->id }}]" value="1"
                                   class="w-4 h-4 accent-amber-500">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Admin kapely (všetky práva)</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-2">
                            @foreach(\App\Models\Role::PERMISSION_GROUPS as $group => $perms)
                            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ $group }}</p>
                                <div class="space-y-1.5">
                                    @foreach($perms as $perm)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="band_permissions[{{ $band->id }}][]" value="{{ $perm }}"
                                               class="w-4 h-4 accent-amber-500">
                                        <span class="text-xs text-slate-700 dark:text-slate-300">{{ \App\Models\Role::PERMISSION_LABELS[$perm] }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 pt-2 border-t border-slate-100 dark:border-slate-700">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                Vytvoriť používateľa
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="px-5 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors text-sm text-center">
                Zrušiť
            </a>
        </div>
    </form>
</div>
<script>
function togglePw(fieldId, btn) {
    const inp = document.getElementById(fieldId);
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.querySelectorAll('.eye-open').forEach(el => el.style.display = show ? 'none' : '');
    btn.querySelectorAll('.eye-shut').forEach(el => el.style.display = show ? '' : 'none');
}
</script>
@endsection
