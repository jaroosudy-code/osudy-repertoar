@extends('layouts.app')
@section('title', 'Správa kapiel')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Kapely</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Správa kapiel a ich členov</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.users.index') }}"
           class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-medium transition-colors">
            Používatelia
        </a>
        <a href="{{ route('admin.bands.create') }}"
           class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
            + Nová kapela
        </a>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    @if($bands->isEmpty())
        <div class="text-center py-12 text-slate-400">
            <p>Zatiaľ žiadne kapely.</p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Kapela</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Popis</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Členov</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Piesní</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Playlistov</th>
                    <th class="text-right px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($bands as $band)
                <tr class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">
                        {{ $band->name }}
                        <span class="ml-1 text-xs text-slate-400 font-mono">{{ $band->slug }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs">{{ $band->description ?: '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $band->users_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $band->songs_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">{{ $band->setlists_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.bands.edit', $band) }}"
                           class="text-slate-500 dark:text-slate-400 hover:text-amber-600 font-medium transition-colors mr-3">Upraviť</a>
                        <form method="POST" action="{{ route('admin.bands.destroy', $band) }}" class="inline delete-band-form" data-name="{{ $band->name }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-500 dark:text-slate-400 hover:text-red-600 font-medium transition-colors">Zmazať</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
<script>
document.querySelectorAll('.delete-band-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm('Zmazať kapelu „' + form.dataset.name + '"?')) form.submit();
    });
});
</script>
@endsection
