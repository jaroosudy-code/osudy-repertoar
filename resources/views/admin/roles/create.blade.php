@extends('layouts.app')
@section('title', 'Nová rola')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.roles.index') }}" class="text-slate-400 hover:text-slate-600 text-sm">← Späť na roly</a>
</div>
<h1 class="text-2xl font-bold text-slate-800 mb-6">Nová rola</h1>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-6">
        @csrf

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Názov roly <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('name') border-red-400 @enderror"
                   placeholder="napr. Hosť">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <p class="text-sm font-medium text-slate-700 mb-4">Oprávnenia</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach(\App\Models\Role::PERMISSION_GROUPS as $group => $perms)
                <div class="bg-slate-50 rounded-lg p-4">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">{{ $group }}</p>
                    <div class="space-y-2.5">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                   {{ in_array($perm, old('permissions', [])) ? 'checked' : '' }}
                                   class="w-4 h-4 accent-amber-500 cursor-pointer">
                            <span class="text-sm text-slate-700 group-hover:text-slate-900">
                                {{ \App\Models\Role::PERMISSION_LABELS[$perm] }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 pt-2 border-t border-slate-100">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                Vytvoriť rolu
            </button>
            <a href="{{ route('admin.roles.index') }}"
               class="px-5 py-2.5 border border-slate-300 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors text-sm text-center">
                Zrušiť
            </a>
        </div>
    </form>
</div>
@endsection
