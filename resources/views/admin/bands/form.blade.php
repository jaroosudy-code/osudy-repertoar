@extends('layouts.app')
@section('title', $band ? 'Kapela: ' . $band->name : 'Nová kapela')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.bands.index') }}" class="text-slate-400 hover:text-slate-600 text-sm">← Späť na kapely</a>
</div>
<h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6">
    {{ $band ? 'Kapela: ' . $band->name : 'Nová kapela' }}
</h1>

{{-- Základné info --}}
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
    <form method="POST"
          action="{{ $band ? route('admin.bands.update', $band) : route('admin.bands.store') }}"
          class="space-y-5">
        @csrf
        @if($band) @method('PATCH') @endif

        <div class="sm:w-1/2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Názov kapely <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $band?->name) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('name') border-red-400 @enderror">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="sm:w-2/3">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Popis</label>
            <input type="text" name="description" value="{{ old('description', $band?->description) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>

        <div class="flex gap-3 pt-2 border-t border-slate-100 dark:border-slate-700">
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                {{ $band ? 'Uložiť zmeny' : 'Vytvoriť kapelu' }}
            </button>
            <a href="{{ route('admin.bands.index') }}"
               class="px-5 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors text-sm text-center">
                Zrušiť
            </a>
        </div>
    </form>
</div>

@if($band)
{{-- Členovia --}}
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Členovia kapely</h2>

    @if($band->users->isEmpty())
        <p class="text-slate-400 text-sm">Kapela zatiaľ nemá žiadnych členov.</p>
    @else
        <div class="space-y-4">
            @foreach($band->users as $member)
            <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $member->name }}</span>
                        <span class="text-xs text-slate-400 ml-2">{{ $member->email }}</span>
                        @if($member->role)
                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium {{ $member->role->badgeClass() }}">
                                {{ $member->role->name }}
                            </span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.bands.detach-user', [$band, $member]) }}"
                          class="inline" onsubmit="return confirm('Odobrať {{ $member->name }} z kapely?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">Odobrať</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.bands.update-user-permissions', [$band, $member]) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_band_admin" value="1"
                                   {{ $member->pivot->is_band_admin ? 'checked' : '' }}
                                   class="w-4 h-4 accent-amber-500">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Admin kapely (všetky práva)</span>
                        </label>
                    </div>

                    @php
                        $memberPerms = $member->pivot->permissions;
                        if (is_string($memberPerms)) $memberPerms = json_decode($memberPerms, true) ?? [];
                        $memberPerms = $memberPerms ?? [];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-3">
                        @foreach(\App\Models\Role::PERMISSION_GROUPS as $group => $perms)
                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ $group }}</p>
                            <div class="space-y-1.5">
                                @foreach($perms as $perm)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                           {{ in_array($perm, $memberPerms) ? 'checked' : '' }}
                                           class="w-4 h-4 accent-amber-500">
                                    <span class="text-xs text-slate-700 dark:text-slate-300">
                                        {{ \App\Models\Role::PERMISSION_LABELS[$perm] }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="submit"
                            class="text-xs bg-slate-100 dark:bg-slate-700 hover:bg-amber-100 dark:hover:bg-amber-900/30 text-slate-700 dark:text-slate-300 hover:text-amber-700 dark:hover:text-amber-400 px-3 py-1.5 rounded-lg transition-colors">
                        Uložiť práva
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Pridať člena --}}
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Pridať člena</h2>

    @php
        $existingIds = $band->users->pluck('id')->toArray();
        $availableUsers = $allUsers->reject(fn($u) => in_array($u->id, $existingIds));
    @endphp

    @if($availableUsers->isEmpty())
        <p class="text-slate-400 text-sm">Všetci používatelia sú už v kapele.</p>
    @else
    <form method="POST" action="{{ route('admin.bands.attach-user', $band) }}" class="space-y-4">
        @csrf
        <div class="sm:w-1/3">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Používateľ</label>
            <select name="user_id" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                @foreach($availableUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer mb-3">
                <input type="checkbox" name="is_band_admin" value="1" class="w-4 h-4 accent-amber-500">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Admin kapely</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach(\App\Models\Role::PERMISSION_GROUPS as $group => $perms)
                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ $group }}</p>
                    <div class="space-y-1.5">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="w-4 h-4 accent-amber-500">
                            <span class="text-xs text-slate-700 dark:text-slate-300">
                                {{ \App\Models\Role::PERMISSION_LABELS[$perm] }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-5 py-2.5 rounded-lg transition-colors text-sm">
            Pridať do kapely
        </button>
    </form>
    @endif
</div>
@endif
@endsection
