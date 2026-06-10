@extends('layouts.app')
@section('title', $setlist->name)

@section('content')
@php
    $isEntertainment = $setlist->event_type === 'entertainment';
    $csrfToken = csrf_token();
@endphp

{{-- Header --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('setlists.index') }}" class="text-slate-400 hover:text-slate-600">← Späť</a>
        <h1 class="text-xl font-bold text-slate-800">{{ $setlist->name }}</h1>
        <span class="px-2 py-0.5 rounded-full text-xs font-medium
            {{ $isEntertainment ? 'bg-pink-100 text-pink-700' : 'bg-indigo-100 text-indigo-700' }}">
            {{ $isEntertainment ? '🎉 Zábava' : '🎤 Koncert' }}
        </span>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('setlists.export.csv', $setlist) }}"
           class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium rounded-lg transition-colors">
            ⬇ Export CSV
        </a>
    </div>
</div>

{{-- Two-column layout --}}
<div class="flex gap-4 h-[calc(100vh-160px)]">

@if($canEdit)
    {{-- LEFT: Song Library --}}
    <div class="w-72 shrink-0 flex flex-col bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-3 border-b border-slate-200 bg-slate-50">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Knižnica piesní</p>
            <input type="text" id="lib-search" placeholder="Hľadať…"
                   class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <div class="flex gap-1 mt-2">
                <select id="lib-tempo" class="flex-1 border border-slate-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-amber-400">
                    <option value="">Všetky</option>
                    <option value="fast">Rýchle</option>
                    <option value="slow">Pomalé</option>
                </select>
                <select id="lib-type" class="flex-1 border border-slate-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-amber-400">
                    <option value="">Všetky</option>
                    <option value="own">Vlastné</option>
                    <option value="cover">Covery</option>
                </select>
            </div>
        </div>
        <div id="song-library" class="flex-1 overflow-y-auto p-2 space-y-1">
            @foreach($allSongs as $song)
            <div class="library-song flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50 hover:bg-amber-50 hover:border-amber-300 cursor-grab active:cursor-grabbing transition-colors select-none"
                 data-song-id="{{ $song->id }}"
                 data-duration="{{ $song->duration_seconds }}"
                 data-name="{{ strtolower($song->name) }}"
                 data-tempo="{{ $song->tempo }}"
                 data-type="{{ $song->type }}">
                <span class="inline-block w-3 h-3 rounded-full shrink-0 border border-slate-300"
                      style="background-color: {{ $song->color }}"></span>
                <span class="flex-1 text-sm font-medium text-slate-700 truncate">{{ $song->name }}</span>
                <span class="text-xs text-slate-400 font-mono shrink-0">{{ $song->duration_formatted }}</span>
            </div>
            @endforeach
        </div>
    </div>
@endif

    {{-- RIGHT: Setlist Builder --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        <div class="flex-1 overflow-y-auto space-y-4" id="builder-area">

        @if($isEntertainment)
            {{-- Grand total bar --}}
            <div class="bg-slate-800 text-white rounded-xl px-4 py-2.5 flex items-center justify-between sticky top-0 z-10 shadow">
                <span class="text-sm font-medium">Celkový čas hudby:</span>
                <span id="grand-total-music" class="font-mono font-bold text-amber-400 text-lg">0:00:00</span>
                <span class="text-slate-400 text-sm mx-2">|</span>
                <span class="text-sm font-medium">S prestávkami:</span>
                <span id="grand-total-all" class="font-mono font-bold text-white text-lg">0:00:00</span>
            </div>

            {{-- Rounds --}}
            <div id="rounds-container" data-setlist-id="{{ $setlist->id }}">
                @foreach($rounds as $round)
                <div class="round-block bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
                     data-round-id="{{ $round->id }}">
                    <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border-b border-slate-200">
                        @if($canEdit)<span class="drag-round-handle cursor-grab text-slate-400 hover:text-slate-600 text-xl">⠿</span>@endif
                        <span class="round-name font-semibold text-slate-700 flex-1"
                              {{ $canEdit ? 'contenteditable=true' : '' }}
                              data-round-id="{{ $round->id }}">{{ $round->name }}</span>
                        <span class="round-time font-mono text-sm font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">0:00:00</span>
                        @if($canEdit)
                        <button onclick="deleteRound({{ $round->id }}, this)"
                                class="text-slate-400 hover:text-red-500 transition-colors ml-2 text-lg leading-none"
                                title="Zmazať kolo">×</button>
                        @endif
                    </div>
                    <div class="{{ $canEdit ? 'songs-drop-zone' : '' }} p-2 min-h-[60px] space-y-1"
                         data-round-id="{{ $round->id }}"
                         data-setlist-id="{{ $setlist->id }}">
                        @foreach($round->setlistSongs as $entry)
                        <div class="song-entry flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white {{ $canEdit ? 'cursor-grab active:cursor-grabbing' : '' }} select-none"
                             data-entry-id="{{ $entry->id }}"
                             data-duration="{{ $entry->song->duration_seconds }}">
                            @if($canEdit)<span class="drag-handle text-slate-300 hover:text-slate-500 cursor-grab text-lg">⠿</span>@endif
                            <span class="inline-block w-3 h-3 rounded-full shrink-0 border border-slate-300"
                                  style="background-color: {{ $entry->song->color }}"></span>
                            <a href="{{ route('songs.show', $entry->song) }}" class="flex-1 text-sm font-medium text-slate-700 truncate hover:text-amber-600">{{ $entry->song->name }}</a>
                            <span class="text-xs font-mono text-slate-400 shrink-0">{{ $entry->song->duration_formatted }}</span>
                            @if($entry->song->tempo === 'fast')
                                <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">⚡</span>
                            @else
                                <span class="text-xs bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded">🌊</span>
                            @endif
                            @if($canEdit)
                            <button onclick="removeEntry({{ $entry->id }}, this)"
                                    class="text-slate-300 hover:text-red-500 transition-colors ml-1 text-lg leading-none shrink-0">×</button>
                            @endif
                        </div>
                        @endforeach
                        @if($round->setlistSongs->isEmpty() && $canEdit)
                        <div class="drop-hint text-center text-slate-300 py-4 text-sm select-none pointer-events-none">
                            Pretiahnite sem piesne z knižnice
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 px-4 py-2 bg-slate-50 border-t border-slate-100">
                        @if($canEdit)
                        <span class="text-xs text-slate-400">Prestávka po kole:</span>
                        <input type="number" min="0" max="120" value="{{ $round->break_after_minutes }}"
                               class="w-14 border border-slate-300 rounded px-2 py-0.5 text-sm text-center focus:outline-none focus:ring-1 focus:ring-amber-400"
                               onchange="updateBreak({{ $round->id }}, this.value)">
                        <span class="text-xs text-slate-400">min</span>
                        @endif
                        <span class="break-badge {{ $canEdit ? 'ml-1' : '' }} text-xs text-pink-600 font-medium"
                              style="{{ $round->break_after_minutes > 0 ? '' : 'display:none' }}">
                            ☕ {{ $round->break_after_minutes }} min prestávka
                        </span>
                    </div>
                </div>
                @endforeach
            </div>

            @if($canEdit)
            <button onclick="addRound()"
                    class="w-full border-2 border-dashed border-slate-300 hover:border-amber-400 text-slate-400 hover:text-amber-500 rounded-xl py-3 text-sm font-medium transition-colors">
                + Pridať kolo
            </button>
            @endif

        @else
            {{-- CONCERT: single flat list --}}
            <div class="bg-slate-800 text-white rounded-xl px-4 py-2.5 flex items-center justify-between sticky top-0 z-10 shadow">
                <span class="text-sm font-medium">Celkový čas:</span>
                <span id="grand-total-music" class="font-mono font-bold text-amber-400 text-lg">0:00:00</span>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200">
                    <span class="font-semibold text-slate-700">Program koncertu</span>
                </div>
                <div class="{{ $canEdit ? 'songs-drop-zone' : '' }} p-2 min-h-[120px] space-y-1"
                     data-round-id=""
                     data-setlist-id="{{ $setlist->id }}">
                    @foreach($entries ?? [] as $entry)
                    <div class="song-entry flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white {{ $canEdit ? 'cursor-grab active:cursor-grabbing hover:border-slate-300' : '' }} select-none"
                         data-entry-id="{{ $entry->id }}"
                         data-duration="{{ $entry->song->duration_seconds }}">
                        @if($canEdit)<span class="drag-handle text-slate-300 hover:text-slate-500 cursor-grab text-lg">⠿</span>@endif
                        <span class="inline-block w-3 h-3 rounded-full shrink-0 border border-slate-300"
                              style="background-color: {{ $entry->song->color }}"></span>
                        <a href="{{ route('songs.show', $entry->song) }}" class="flex-1 text-sm font-medium text-slate-700 truncate hover:text-amber-600">{{ $entry->song->name }}</a>
                        <span class="text-xs font-mono text-slate-400 shrink-0">{{ $entry->song->duration_formatted }}</span>
                        @if($entry->song->tempo === 'fast')
                            <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">⚡</span>
                        @else
                            <span class="text-xs bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded">🌊</span>
                        @endif
                        @if($canEdit)
                        <button onclick="removeEntry({{ $entry->id }}, this)"
                                class="text-slate-300 hover:text-red-500 transition-colors ml-1 text-lg leading-none shrink-0">×</button>
                        @endif
                    </div>
                    @endforeach
                    @if($canEdit)
                    <div class="drop-hint text-center text-slate-300 py-4 text-sm select-none pointer-events-none"
                         {{ isset($entries) && $entries->isNotEmpty() ? 'style=display:none' : '' }}>
                        Pretiahnite sem piesne z knižnice
                    </div>
                    @endif
                </div>
            </div>
        @endif

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
const SETLIST_ID = {{ $setlist->id }};
const CSRF = '{{ $csrfToken }}';
const IS_ENTERTAINMENT = {{ $isEntertainment ? 'true' : 'false' }};
const CAN_EDIT = {{ $canEdit ? 'true' : 'false' }};

function fmtTime(s) {
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return `${h}:${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`;
}

function updateTotals() {
    const musicTotal = Array.from(document.querySelectorAll('.songs-drop-zone .song-entry'))
        .reduce((sum, el) => sum + parseInt(el.dataset.duration || 0), 0);
    document.getElementById('grand-total-music').textContent = fmtTime(musicTotal);

    if (IS_ENTERTAINMENT) {
        // Per-round times
        document.querySelectorAll('.round-block').forEach(block => {
            const roundSec = Array.from(block.querySelectorAll('.song-entry'))
                .reduce((sum, el) => sum + parseInt(el.dataset.duration || 0), 0);
            const timeEl = block.querySelector('.round-time');
            if (timeEl) timeEl.textContent = fmtTime(roundSec);
        });

        const breakTotal = Array.from(document.querySelectorAll('.round-block input[type=number]'))
            .reduce((sum, el) => sum + parseInt(el.value || 0) * 60, 0);
        document.getElementById('grand-total-all').textContent = fmtTime(musicTotal + breakTotal);
    }

    // Show/hide drop hint
    document.querySelectorAll('.songs-drop-zone').forEach(zone => {
        const hint = zone.querySelector('.drop-hint');
        if (hint) hint.style.display = zone.querySelectorAll('.song-entry').length ? 'none' : '';
    });
}

async function apiPost(url, body) {
    const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(body)
    });
    return r.json();
}
async function apiPatch(url, body) {
    const r = await fetch(url, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(body)
    });
    return r.json();
}
async function apiDelete(url) {
    const r = await fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    return r.json();
}

function buildEntryEl(data) {
    const div = document.createElement('div');
    div.className = 'song-entry flex items-center gap-2 p-2 rounded-lg border border-slate-200 hover:border-slate-300 bg-white cursor-grab active:cursor-grabbing select-none';
    div.dataset.entryId = data.id;
    div.dataset.duration = data.duration_seconds;
    div.innerHTML = `
        <span class="drag-handle text-slate-300 hover:text-slate-500 cursor-grab text-lg">⠿</span>
        <span class="inline-block w-3 h-3 rounded-full shrink-0 border border-slate-300" style="background-color:${data.color}"></span>
        <span class="flex-1 text-sm font-medium text-slate-700 truncate">${data.name}</span>
        <span class="text-xs font-mono text-slate-400 shrink-0">${data.duration_formatted}</span>
        <span class="text-xs px-1.5 py-0.5 rounded ${data.tempo==='fast'?'bg-blue-100 text-blue-600':'bg-orange-100 text-orange-600'}">${data.tempo==='fast'?'⚡':'🌊'}</span>
        <button onclick="removeEntry(${data.id},this)" class="text-slate-300 hover:text-red-500 transition-colors ml-1 text-lg leading-none shrink-0">×</button>
    `;
    return div;
}

function reorderZone(zone) {
    const roundId = zone.dataset.roundId || null;
    const entries = Array.from(zone.querySelectorAll('.song-entry')).map((el, idx) => ({
        id: parseInt(el.dataset.entryId),
        round_id: roundId ? parseInt(roundId) : null,
        position: idx
    }));
    if (entries.length) {
        apiPatch(`/setlists/${SETLIST_ID}/songs/reorder`, { entries });
    }
}

function initDropZone(zone) {
    Sortable.create(zone, {
        group: 'songs',
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'opacity-40',
        onAdd(evt) {
            const libSong = evt.item;
            const songId = libSong.dataset.songId;
            if (!songId) return;
            const roundId = zone.dataset.roundId || null;

            // Remove the cloned library item; we'll insert proper entry
            libSong.remove();

            apiPost(`/setlists/${SETLIST_ID}/songs`, { song_id: parseInt(songId), round_id: roundId ? parseInt(roundId) : null })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    if (data.id) {
                        const el = buildEntryEl(data);
                        zone.appendChild(el);
                        updateTotals();
                    }
                });
        },
        onEnd(evt) {
            // Cross-zone move: update source zone order too
            if (evt.from !== evt.to) reorderZone(evt.from);
            reorderZone(evt.to);
            updateTotals();
        },
        onSort(evt) {
            if (evt.from === evt.to) {
                reorderZone(evt.to);
                updateTotals();
            }
        }
    });
}

// Initialize library as clone-source (only when canEdit)
if (CAN_EDIT && document.getElementById('song-library')) {
    Sortable.create(document.getElementById('song-library'), {
        group: { name: 'songs', pull: 'clone', put: false },
        sort: false,
        animation: 150,
        ghostClass: 'opacity-40',
    });
}

// Initialize all drop zones (only when canEdit)
if (CAN_EDIT) {
    document.querySelectorAll('.songs-drop-zone').forEach(initDropZone);
}

// Remove entry
async function removeEntry(entryId, btn) {
    await apiDelete(`/setlists/${SETLIST_ID}/songs/${entryId}`);
    btn.closest('.song-entry').remove();
    updateTotals();
}

// Delete round
async function deleteRound(roundId, btn) {
    if (!confirm('Zmazať toto kolo aj so všetkými piesňami?')) return;
    await apiDelete(`/setlists/${SETLIST_ID}/rounds/${roundId}`);
    btn.closest('.round-block').remove();
    updateTotals();
}

// Add round
async function addRound() {
    const data = await apiPost(`/setlists/${SETLIST_ID}/rounds`, {});
    if (!data.id) return;

    const block = document.createElement('div');
    block.className = 'round-block bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden';
    block.dataset.roundId = data.id;
    block.innerHTML = `
        <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="drag-round-handle cursor-grab text-slate-400 hover:text-slate-600 text-xl">⠿</span>
            <span class="round-name font-semibold text-slate-700 flex-1" contenteditable="true" data-round-id="${data.id}">${data.name}</span>
            <span class="round-time font-mono text-sm font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">0:00:00</span>
            <button onclick="deleteRound(${data.id},this)" class="text-slate-400 hover:text-red-500 transition-colors ml-2 text-lg leading-none" title="Zmazať kolo">×</button>
        </div>
        <div class="songs-drop-zone p-2 min-h-[60px] space-y-1" data-round-id="${data.id}" data-setlist-id="${SETLIST_ID}">
            <div class="drop-hint text-center text-slate-300 py-4 text-sm select-none pointer-events-none">Pretiahnite sem piesne z knižnice</div>
        </div>
        <div class="flex items-center gap-3 px-4 py-2 bg-slate-50 border-t border-slate-100">
            <span class="text-xs text-slate-400">Prestávka po kole:</span>
            <input type="number" min="0" max="120" value="15"
                   class="w-14 border border-slate-300 rounded px-2 py-0.5 text-sm text-center focus:outline-none focus:ring-1 focus:ring-amber-400"
                   onchange="updateBreak(${data.id},this.value)">
            <span class="text-xs text-slate-400">min</span>
        </div>
    `;

    document.getElementById('rounds-container').appendChild(block);
    initDropZone(block.querySelector('.songs-drop-zone'));

    // Editable round name
    block.querySelector('.round-name').addEventListener('blur', function () {
        apiPatch(`/setlists/${SETLIST_ID}/rounds/${data.id}`, { name: this.textContent.trim() });
    });
}

// Update break time
function updateBreak(roundId, minutes) {
    apiPatch(`/setlists/${SETLIST_ID}/rounds/${roundId}`, { break_after_minutes: parseInt(minutes) });
    updateTotals();
}

// Editable round names (for existing rounds)
document.querySelectorAll('.round-name[contenteditable]').forEach(el => {
    el.addEventListener('blur', function () {
        const roundId = this.dataset.roundId;
        apiPatch(`/setlists/${SETLIST_ID}/rounds/${roundId}`, { name: this.textContent.trim() });
    });
    el.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); el.blur(); } });
});

// Library filter
const libSearch = document.getElementById('lib-search');
const libTempo = document.getElementById('lib-tempo');
const libType = document.getElementById('lib-type');

function filterLibrary() {
    const q = libSearch.value.toLowerCase();
    const tempo = libTempo.value;
    const type = libType.value;
    document.querySelectorAll('.library-song').forEach(el => {
        const show = (!q || el.dataset.name.includes(q)) &&
                     (!tempo || el.dataset.tempo === tempo) &&
                     (!type || el.dataset.type === type);
        el.style.display = show ? '' : 'none';
    });
}

libSearch.addEventListener('input', filterLibrary);
libTempo.addEventListener('change', filterLibrary);
libType.addEventListener('change', filterLibrary);

// Initial totals
updateTotals();
</script>
@endsection
