@extends('layouts.app')
@section('title', 'Správa používateľov')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Používatelia</h1>
        <p class="text-sm text-slate-500 mt-0.5">Spravuj prístupy členov kapely</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('admin.roles.index') }}"
           class="px-4 py-2 border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-lg text-sm font-medium transition-colors">
            Správa rolí
        </a>
        <a href="{{ route('admin.users.create') }}"
           class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
            + Nový používateľ
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    @if($users->isEmpty())
        <div class="text-center py-12 text-slate-400">
            <p>Zatiaľ žiadni používatelia.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Meno</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Email</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Rola</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors {{ $user->id === auth()->id() ? 'bg-amber-50' : '' }}">
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                                <span class="ml-1 text-xs text-amber-600">(ty)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @if($user->role)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $user->role->badgeClass() }}">
                                    {{ $user->role->name }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-slate-500 hover:text-amber-600 font-medium transition-colors mr-3">Upraviť</a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                  onsubmit="return confirm('Zmazať používateľa „{{ $user->name }}"?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-500 hover:text-red-600 font-medium transition-colors">Zmazať</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
