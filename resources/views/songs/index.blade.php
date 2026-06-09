@extends('layouts.app')
@section('title', 'Piesne')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Piesne ({{ $songs->count() }})</h1>
    <a href="{{ route('songs.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-lg transition-colors">
        + Pridať pieseň
    </a>
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
        <p class="text-lg">Zatiaľ žiadne piesne. <a href="{{ route('songs.create') }}" class="text-amber-500 hover:underline">Pridaj prvú!</a></p>
    </div>
@else
    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap gap-2 items-center">
        <input type="text" id="search" placeholder="Hľadať pieseň…"
               class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-56">
        <select id="filter-tempo" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">Všetky tempá</option>
            <option value="fast">Rýchle</option>
            <option value="slow">Pomalé</option>
        </select>
        <select id="filter-type" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">Vlastné aj covery</option>
            <option value="own">Vlastné</option>
            <option value="cover">Covery</option>
        </select>
        <span id="count-display" class="text-sm text-slate-500 ml-2"></span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 w-6"></th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Názov</th>
                    <th class="col-desktop text-left px-4 py-3 font-semibold text-slate-600">Čas</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Tempo</th>
                    <th class="col-desktop text-left px-4 py-3 font-semibold text-slate-600">BPM</th>
                    <th class="col-desktop text-left px-4 py-3 font-semibold text-slate-600">Typ</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600"></th>
                </tr>
            </thead>
            <tbody id="songs-table">
                @foreach($songs as $song)
                <tr class="song-row border-b border-slate-100 hover:bg-slate-50 transition-colors"
                    data-name="{{ strtolower($song->name) }}"
                    data-tempo="{{ $song->tempo }}"
                    data-type="{{ $song->type }}">
                    <td class="px-4 py-3">
                        <span class="inline-block w-4 h-4 rounded-full border border-slate-300"
                              style="background-color: {{ $song->color }}"></span>
                    </td>
                    <td class="px-4 py-3 font-medium">
                        <a href="{{ route('songs.show', $song) }}" class="text-slate-800 hover:text-amber-600 transition-colors">{{ $song->name }}</a>
                    </td>
                    <td class="col-desktop px-4 py-3 text-slate-600 font-mono">{{ $song->duration_formatted }}</td>
                    <td class="px-4 py-3">
                        @if($song->tempo === 'fast')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Rýchla</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Pomalá</span>
                        @endif
                    </td>
                    <td class="col-desktop px-4 py-3 text-slate-600 font-mono">{{ $song->bpm ?? '' }}</td>
                    <td class="col-desktop px-4 py-3">
                        @if($song->type === 'own')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Osudy</span>
                        @else
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">Cover</span>
                                @if($song->original_artist)
                                    <span class="text-xs text-slate-400 ml-1">{{ $song->original_artist }}</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        {{-- Desktop: text --}}
                        <a href="{{ route('songs.edit', $song) }}"
                           class="act-desktop text-slate-500 hover:text-amber-600 font-medium mr-3 transition-colors">Upraviť</a>
                        <form method="POST" action="{{ route('songs.destroy', $song) }}" class="act-desktop"
                              onsubmit="return confirm('Naozaj zmazať pieseň „{{ $song->name }}"?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-500 hover:text-red-600 font-medium transition-colors">Zmazať</button>
                        </form>
                        {{-- Mobile: ikony --}}
                        <a href="{{ route('songs.edit', $song) }}"
                           class="act-mobile items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-500 mr-1"
                           title="Upraviť">✏️</a>
                        <form method="POST" action="{{ route('songs.destroy', $song) }}" style="display:inline"
                              onsubmit="return confirm('Naozaj zmazať pieseň „{{ $song->name }}"?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="act-mobile items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-500"
                                    title="Zmazať">🗑️</button>
                        </form>
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

function applyFilters() {
    const q = search?.value.toLowerCase() ?? '';
    const tempo = filterTempo?.value ?? '';
    const type = filterType?.value ?? '';
    const rows = document.querySelectorAll('.song-row');
    let visible = 0;

    rows.forEach(row => {
        const match =
            (!q || row.dataset.name.includes(q)) &&
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
</script>
@endsection
