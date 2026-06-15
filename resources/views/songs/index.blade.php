@extends('layouts.app')
@section('title', 'Piesne')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Piesne ({{ $songs->count() }})</h1>
    @if(auth()->user()->hasPermission('songs.create'))
    <a href="{{ route('songs.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
        + Pridať pieseň
    </a>
    @endif
</div>

<style>
@media (max-width: 639px) {
    .col-desktop { display: none; }
    .act-desktop { display: none !important; }
    .act-mobile  { display: inline-flex !important; }
}
@media (min-width: 640px) {
    .act-desktop { display: inline; }
    .act-mobile  { display: none !important; }
}
</style>

@if($songs->isEmpty())
    <div class="text-center py-16 text-slate-400">
        <div class="text-5xl mb-3">🎵</div>
        <p class="text-lg">Zatiaľ žiadne piesne.
            @if(auth()->user()->hasPermission('songs.create'))
                <a href="{{ route('songs.create') }}" class="text-amber-500 hover:underline">Pridaj prvú!</a>
            @endif
        </p>
    </div>
@else
    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap gap-2 items-center">
        <input type="text" id="search" placeholder="Hľadať pieseň…"
               class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-56">
        <select id="filter-tempo" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">Všetky tempá</option>
            <option value="fast">Rýchle</option>
            <option value="slow">Pomalé</option>
        </select>
        <select id="filter-type" class="border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">Vlastné aj covery</option>
            <option value="own">Vlastné</option>
            <option value="cover">Covery</option>
        </select>
        <span id="count-display" class="text-sm text-slate-500 dark:text-slate-400 ml-2"></span>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden sm:overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400 w-6"></th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Názov</th>
                    <th class="col-desktop text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Čas</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Tempo</th>
                    <th class="col-desktop text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">BPM</th>
                    <th class="col-desktop text-left px-4 py-3 font-semibold text-slate-600 dark:text-slate-400">Typ</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600 dark:text-slate-400"></th>
                </tr>
            </thead>
            <tbody id="songs-table">
                @foreach($songs as $song)
                <tr class="song-row border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors"
                    data-name="{{ strtolower($song->name) }}"
                    data-tempo="{{ $song->tempo }}"
                    data-type="{{ $song->type }}">
                    <td class="px-4 py-3">
                        <span class="inline-block w-4 h-4 rounded-full border border-slate-300"
                              style="background-color: {{ $song->color }}"></span>
                    </td>
                    <td class="px-4 py-3 font-medium">
                        <a href="{{ route('songs.show', $song) }}" class="text-slate-800 dark:text-slate-200 hover:text-amber-600 transition-colors">{{ $song->name }}</a>
                    </td>
                    <td class="col-desktop px-4 py-3 text-slate-600 dark:text-slate-400 font-mono">{{ $song->duration_formatted }}</td>
                    <td class="px-4 py-3">
                        @if($song->tempo === 'fast')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Rýchla</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Pomalá</span>
                        @endif
                    </td>
                    <td class="col-desktop px-4 py-3 font-mono">
                        @if($song->bpm)
                        <button onclick="openMModal({{ $song->bpm }}, {{ json_encode($song->name) }})"
                                class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 hover:underline transition-colors cursor-pointer font-mono">
                            {{ $song->bpm }}
                        </button>
                        @endif
                    </td>
                    <td class="col-desktop px-4 py-3">
                        @if($song->type === 'own')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Osudy</span>
                        @else
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">Cover</span>
                                @if($song->original_artist)
                                    <span class="text-xs text-slate-400 dark:text-slate-500 ml-1">{{ $song->original_artist }}</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if(auth()->user()->hasPermission('songs.edit'))
                        {{-- Desktop --}}
                        <a href="{{ route('songs.edit', $song) }}"
                           class="act-desktop text-slate-500 dark:text-slate-400 hover:text-amber-600 font-medium mr-3 transition-colors">Upraviť</a>
                        {{-- Mobile --}}
                        <a href="{{ route('songs.edit', $song) }}"
                           class="act-mobile items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-500 mr-1"
                           title="Upraviť">✏️</a>
                        @endif
                        @if(auth()->user()->hasPermission('songs.delete'))
                        {{-- Desktop --}}
                        <form method="POST" action="{{ route('songs.destroy', $song) }}" class="act-desktop delete-song-form" data-name="{{ $song->name }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-500 dark:text-slate-400 hover:text-red-600 font-medium transition-colors">Zmazať</button>
                        </form>
                        {{-- Mobile --}}
                        <form method="POST" action="{{ route('songs.destroy', $song) }}" style="display:inline" class="delete-song-form" data-name="{{ $song->name }}">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="act-mobile items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-500"
                                    title="Zmazať">🗑️</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<script>
const search = document.getElementById('search');
const filterTempo = document.getElementById('filter-tempo');
const filterType = document.getElementById('filter-type');
const countDisplay = document.getElementById('count-display');

function norm(str) {
    return str.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
}

function applyFilters() {
    const q = norm(search?.value ?? '');
    const tempo = filterTempo?.value ?? '';
    const type = filterType?.value ?? '';
    const rows = document.querySelectorAll('.song-row');
    let visible = 0;

    rows.forEach(row => {
        const match =
            (!q || norm(row.dataset.name).includes(q)) &&
            (!tempo || row.dataset.tempo === tempo) &&
            (!type || row.dataset.type === type);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    if (countDisplay) countDisplay.textContent = visible !== rows.length ? `Zobrazené: ${visible} / ${rows.length}` : '';
}

search?.addEventListener('input', applyFilters);
filterTempo?.addEventListener('change', applyFilters);
filterType?.addEventListener('change', applyFilters);

document.querySelectorAll('.delete-song-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm('Naozaj zmazať pieseň „' + form.dataset.name + '"?')) {
            form.submit();
        }
    });
});
</script>

@include('partials._metronom_modal')
@endsection
