@extends('layouts.app')
@section('title', 'Správa používateľov')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Používatelia</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Spravuj prístupy členov kapely</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('admin.bands.index') }}"
           class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-medium transition-colors">
            Kapely
        </a>
        <a href="{{ route('admin.roles.index') }}"
           class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-medium transition-colors">
            Správa rolí
        </a>
        <a href="{{ route('admin.users.create') }}"
           class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
            + Nový používateľ
        </a>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    @if($users->isEmpty())
        <div class="text-center py-12 text-slate-400">
            <p>Zatiaľ žiadni používatelia.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Meno</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Email</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Rola</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Kapely</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600 dark:text-slate-400"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors {{ $user->id === auth()->id() ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                                <span class="ml-1 text-xs text-amber-600">(ty)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @if($user->role)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $user->role->badgeClass() }}">
                                    {{ $user->role->name }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @forelse($user->bands as $band)
                                <a href="{{ route('admin.bands.edit', $band) }}"
                                   class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-200 transition-colors mr-1">
                                    {{ $band->name }}
                                </a>
                            @empty
                                <span class="text-slate-400 text-xs">—</span>
                            @endforelse
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-slate-500 dark:text-slate-400 hover:text-amber-600 font-medium transition-colors mr-3">Upraviť</a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline delete-confirm-form" data-name="{{ $user->name }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-500 dark:text-slate-400 hover:text-red-600 font-medium transition-colors">Zmazať</button>
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
<script>
document.querySelectorAll('.delete-confirm-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm('Zmazať používateľa „' + form.dataset.name + '"?')) form.submit();
    });
});
</script>
@endsection
