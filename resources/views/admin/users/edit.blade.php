@extends('layouts.app')
@section('title', 'Upraviť používateľa')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-600 text-sm">← Späť na používateľov</a>
</div>
<h1 class="text-2xl font-bold text-slate-800 mb-6">Upraviť: {{ $user->name }}</h1>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
        @csrf @method('PATCH')

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Meno <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" inputmode="email"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('email') border-red-400 @enderror">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Rola <span class="text-red-500">*</span></label>
                <select name="role_id"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('role_id') border-red-400 @enderror">
                    <option value="">– Vyber rolu –</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                Nové heslo <span class="text-slate-400 font-normal text-xs">(nechaj prázdne ak nemeníš)</span>
            </label>
            <div class="flex items-center border border-slate-300 rounded-lg focus-within:ring-2 focus-within:ring-amber-400 @error('password') border-red-400 @enderror">
                <input type="password" name="password" id="pw-edit" autocomplete="new-password"
                       class="flex-1 px-3 py-2.5 text-sm bg-transparent outline-none rounded-l-lg"
                       placeholder="min. 6 znakov">
                <button type="button" onclick="togglePw('pw-edit', this)" tabindex="-1"
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

        <div class="flex gap-3 pt-2 border-t border-slate-100">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                Uložiť zmeny
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="px-5 py-2.5 border border-slate-300 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors text-sm text-center">
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
