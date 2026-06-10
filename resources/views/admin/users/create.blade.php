@extends('layouts.app')
@section('title', 'Nový používateľ')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-600 text-sm">← Späť na používateľov</a>
</div>
<h1 class="text-2xl font-bold text-slate-800 mb-6">Nový používateľ</h1>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Meno <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" autocomplete="off"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('name') border-red-400 @enderror"
                       placeholder="napr. Martin">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" inputmode="email" autocomplete="off"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('email') border-red-400 @enderror"
                       placeholder="martin@skupinaosudy.sk">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Rola <span class="text-red-500">*</span></label>
                <select name="role_id"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('role_id') border-red-400 @enderror">
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
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Heslo <span class="text-red-500">*</span></label>
            <input type="password" name="password" autocomplete="new-password"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('password') border-red-400 @enderror"
                   placeholder="min. 6 znakov">
            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3 pt-2 border-t border-slate-100">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                Vytvoriť používateľa
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="px-5 py-2.5 border border-slate-300 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors text-sm text-center">
                Zrušiť
            </a>
        </div>
    </form>
</div>
@endsection
