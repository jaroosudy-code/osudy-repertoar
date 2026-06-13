@extends('layouts.app')
@section('title', $setlist->name)

@section('content')
@php
    $isEntertainment = $setlist->event_type === 'entertainment';
    $csrfToken = csrf_token();
    $projectionSongs = [];
    $playlistSongIds = [];
    if ($isEntertainment) {
        foreach ($rounds as $round) {
            foreach ($round->setlistSongs as $e) {
                $projectionSongs[] = ['name' => $e->song->name, 'lyrics' => $e->song->lyrics ?? ''];
                $playlistSongIds[] = $e->song_id;
            }
        }
    } else {
        foreach ($entries ?? [] as $e) {
            $projectionSongs[] = ['name' => $e->song->name, 'lyrics' => $e->song->lyrics ?? ''];
            $playlistSongIds[] = $e->song_id;
        }
    }
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
        @if(count($projectionSongs))
        <button onclick="openProjSettings()"
                style="padding:6px 14px;background:#f59e0b;color:#1e293b;border:none;border-radius:8px;font-size:0.875rem;font-weight:600;cursor:pointer;transition:background .15s;"
                onmouseover="this.style.background='#d97706'"
                onmouseout="this.style.background='#f59e0b'">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:-2px;margin-right:5px;"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>Spustiť premietanie
        </button>
        @endif
        <a href="{{ route('setlists.export.csv', $setlist) }}"
           style="padding:6px 14px;background:#16a34a;color:#fff;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .15s;"
           onmouseover="this.style.background='#15803d'"
           onmouseout="this.style.background='#16a34a'">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>Export CSV
        </a>
    </div>
</div>

{{-- Mobile tab bar (hidden on desktop via JS) --}}
@if($canEdit)
<div id="mob-tabs" style="display:none;margin-bottom:12px;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;font-size:0.875rem;font-weight:600;">
    <button id="tab-btn-lib" onclick="showTab('lib')"
            style="flex:1;padding:11px;background:#f59e0b;color:#1e293b;border:none;cursor:pointer;">
        📚 Knižnica
    </button>
    <button id="tab-btn-list" onclick="showTab('list')"
            style="flex:1;padding:11px;background:#f8fafc;color:#475569;border:none;cursor:pointer;">
        🎵 Playlist
    </button>
</div>
@endif

{{-- Two-column layout --}}
<div id="main-layout" class="flex gap-4 h-[calc(100vh-160px)]">

@if($canEdit)
    {{-- LEFT: Song Library --}}
    <div id="panel-lib" class="w-72 shrink-0 flex flex-col bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
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
                 data-type="{{ $song->type }}"
                 data-in-playlist="{{ in_array($song->id, $playlistSongIds) ? '1' : '0' }}">
                <span class="inline-block w-3 h-3 rounded-full shrink-0 border border-slate-300"
                      style="background-color: {{ $song->color }}"></span>
                <span class="flex-1 text-sm font-medium text-slate-700 truncate">{{ $song->name }}</span>
                <span class="text-xs text-slate-400 font-mono shrink-0">{{ $song->duration_formatted }}</span>
                <span class="song-in-playlist" style="display:none;color:#16a34a;font-size:1.1rem;font-weight:bold;flex-shrink:0;">✓</span>
                <button class="mob-add-btn" style="display:none;flex-shrink:0;width:28px;height:28px;border-radius:50%;background:#f59e0b;color:#fff;border:none;font-size:1.2rem;font-weight:bold;cursor:pointer;align-items:center;justify-content:center;line-height:1;"
                        onclick="event.stopPropagation();mobileTapAdd({{ $song->id }},this.closest('.library-song'))">+</button>
            </div>
            @endforeach
        </div>
    </div>
@endif

    {{-- RIGHT: Setlist Builder --}}
    <div id="panel-list" class="flex-1 flex flex-col overflow-hidden">
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
                             data-song-id="{{ $entry->song->id }}"
                             data-duration="{{ $entry->song->duration_seconds }}">
                            @if($canEdit)<span class="drag-handle text-slate-300 hover:text-slate-500 cursor-grab text-lg">⠿</span>@endif
                            <span class="inline-block w-3 h-3 rounded-full shrink-0 border border-slate-300"
                                  style="background-color: {{ $entry->song->color }}"></span>
                            <a href="{{ route('songs.show', $entry->song) }}" class="flex-1 text-sm font-medium text-slate-700 truncate hover:text-amber-600">{{ $entry->song->name }}</a>
                            <span class="text-xs font-mono text-slate-400 shrink-0">{{ $entry->song->duration_formatted }}</span>
                            @if($entry->song->tempo === 'fast')
                                <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">⚡</span>
                            @else
                                <span class="text-xs bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded">🐢</span>
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
                         data-song-id="{{ $entry->song->id }}"
                         data-duration="{{ $entry->song->duration_seconds }}">
                        @if($canEdit)<span class="drag-handle text-slate-300 hover:text-slate-500 cursor-grab text-lg">⠿</span>@endif
                        <span class="inline-block w-3 h-3 rounded-full shrink-0 border border-slate-300"
                              style="background-color: {{ $entry->song->color }}"></span>
                        <a href="{{ route('songs.show', $entry->song) }}" class="flex-1 text-sm font-medium text-slate-700 truncate hover:text-amber-600">{{ $entry->song->name }}</a>
                        <span class="text-xs font-mono text-slate-400 shrink-0">{{ $entry->song->duration_formatted }}</span>
                        @if($entry->song->tempo === 'fast')
                            <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">⚡</span>
                        @else
                            <span class="text-xs bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded">🐢</span>
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
        <span class="text-xs px-1.5 py-0.5 rounded ${data.tempo==='fast'?'bg-blue-100 text-blue-600':'bg-orange-100 text-orange-600'}">${data.tempo==='fast'?'⚡':'🐢'}</span>
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
        delay: 200,
        delayOnTouchOnly: true,
        touchStartThreshold: 5,
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
                        el.dataset.songId = songId;
                        zone.appendChild(el);
                        updateTotals();
                        const libEl = document.querySelector(`.library-song[data-song-id="${songId}"]`);
                        if (libEl) markLibrarySong(libEl, true);
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
        delay: 200,
        touchStartThreshold: 5,
    });
}

// Initialize all drop zones (only when canEdit)
if (CAN_EDIT) {
    document.querySelectorAll('.songs-drop-zone').forEach(initDropZone);
}

// Remove entry
async function removeEntry(entryId, btn) {
    const entry = btn.closest('.song-entry');
    const songId = entry.dataset.songId;
    await apiDelete(`/setlists/${SETLIST_ID}/songs/${entryId}`);
    entry.remove();
    updateTotals();
    if (songId) {
        const stillInPlaylist = document.querySelector(`.song-entry[data-song-id="${songId}"]`);
        const libEl = document.querySelector(`.library-song[data-song-id="${songId}"]`);
        if (libEl) markLibrarySong(libEl, !!stillInPlaylist);
    }
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

// ── Zelené fajky v knižnici ───────────────────────────────────
function markLibrarySong(songEl, inPlaylist) {
    var check = songEl.querySelector('.song-in-playlist');
    var btn   = songEl.querySelector('.mob-add-btn');
    if (check) check.style.display = inPlaylist ? 'inline' : 'none';
    if (btn)   btn.style.display   = (!inPlaylist && window.innerWidth < 768) ? 'inline-flex' : 'none';
    songEl.dataset.inPlaylist = inPlaylist ? '1' : '0';
}

function initLibraryMarkers() {
    document.querySelectorAll('.library-song').forEach(function(el) {
        markLibrarySong(el, el.dataset.inPlaylist === '1');
    });
}

// ── Mobile tabs ──────────────────────────────────────────────
function showTab(tab) {
    var lib  = document.getElementById('panel-lib');
    var list = document.getElementById('panel-list');
    var btnLib  = document.getElementById('tab-btn-lib');
    var btnList = document.getElementById('tab-btn-list');
    if (!lib || !list) return;
    lib.style.display  = tab === 'lib'  ? 'flex' : 'none';
    list.style.display = tab === 'list' ? 'flex' : 'none';
    btnLib.style.background  = tab === 'lib'  ? '#f59e0b' : '#f8fafc';
    btnLib.style.color       = tab === 'lib'  ? '#1e293b' : '#475569';
    btnList.style.background = tab === 'list' ? '#f59e0b' : '#f8fafc';
    btnList.style.color      = tab === 'list' ? '#1e293b' : '#475569';
}

async function mobileTapAdd(songId, songEl) {
    var zone = document.querySelector('.songs-drop-zone');
    if (!zone) return;
    var roundId = zone.dataset.roundId || null;
    var data = await apiPost('/setlists/' + SETLIST_ID + '/songs', {
        song_id: parseInt(songId),
        round_id: roundId ? parseInt(roundId) : null
    });
    if (data.error) { alert(data.error); return; }
    if (data.id) {
        var el = buildEntryEl(data);
        var hint = zone.querySelector('.drop-hint');
        if (hint) zone.insertBefore(el, hint); else zone.appendChild(el);
        updateTotals();
        markLibrarySong(songEl, true);
        showTab('list');
    }
}

// Init mobile layout
if (window.innerWidth < 768 && CAN_EDIT) {
    var _tabs   = document.getElementById('mob-tabs');
    var _layout = document.getElementById('main-layout');
    var _lib    = document.getElementById('panel-lib');
    var _list   = document.getElementById('panel-list');
    if (_tabs)   { _tabs.style.display = 'flex'; }
    if (_layout) { _layout.style.cssText = 'display:block;height:auto;'; }
    if (_lib)    { _lib.style.cssText   = 'width:100%;height:calc(100vh - 230px);flex-direction:column;background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;'; }
    if (_list)   { _list.style.cssText  = 'width:100%;height:calc(100vh - 230px);flex-direction:column;overflow:hidden;'; }
    showTab('list');
}

initLibraryMarkers();
</script>
{{-- ═══════════════ PROJECTION SETTINGS MODAL ═══════════════ --}}
<div id="proj-settings" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:9990;align-items:center;justify-content:center;">
    <div style="background:#1e293b;border-radius:16px;padding:32px;width:420px;max-width:92vw;color:#fff;box-shadow:0 20px 60px rgba(0,0,0,0.6);">
        <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:24px;color:#f1f5f9;">⚙ Nastavenia premietania</h2>

        <div style="margin-bottom:20px;">
            <div style="font-size:0.85rem;color:#94a3b8;margin-bottom:10px;">Veľkosť písma</div>
            <div style="display:flex;align-items:center;gap:10px;">
                <button onclick="projFontAdj(-2)" style="width:38px;height:38px;border-radius:8px;background:#334155;border:none;color:#e2e8f0;font-size:0.85rem;font-weight:700;cursor:pointer;">A-</button>
                <input id="proj-font-range" type="range" min="18" max="48" value="26"
                       oninput="document.getElementById('proj-font-val').textContent=this.value+'px'"
                       style="flex:1;accent-color:#f59e0b;cursor:pointer;">
                <button onclick="projFontAdj(+2)" style="width:38px;height:38px;border-radius:8px;background:#334155;border:none;color:#e2e8f0;font-size:0.85rem;font-weight:700;cursor:pointer;">A+</button>
                <span id="proj-font-val" style="min-width:40px;text-align:right;font-size:0.85rem;color:#e2e8f0;font-family:monospace;">26px</span>
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <div style="font-size:0.85rem;color:#94a3b8;margin-bottom:10px;">Zobraziť akordy</div>
            <div style="display:flex;gap:8px;">
                <button id="proj-ch-yes" onclick="setProjChords(true)"
                        style="flex:1;padding:9px;border-radius:8px;border:2px solid #f59e0b;background:#fffbeb;color:#92400e;font-weight:600;cursor:pointer;transition:all .15s;">Áno</button>
                <button id="proj-ch-no" onclick="setProjChords(false)"
                        style="flex:1;padding:9px;border-radius:8px;border:2px solid transparent;background:#334155;color:#94a3b8;font-weight:600;cursor:pointer;transition:all .15s;">Nie</button>
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <div style="font-size:0.85rem;color:#94a3b8;margin-bottom:10px;">Vynechať refrény <span style="font-size:0.75rem;color:#64748b;">(len pre piesne s [REFRÉN] markerom)</span></div>
            <div style="display:flex;gap:8px;">
                <button id="proj-skip-yes" onclick="setProjSkipRefrain(true)"
                        style="flex:1;padding:9px;border-radius:8px;border:2px solid transparent;background:#334155;color:#94a3b8;font-weight:600;cursor:pointer;transition:all .15s;">Áno</button>
                <button id="proj-skip-no" onclick="setProjSkipRefrain(false)"
                        style="flex:1;padding:9px;border-radius:8px;border:2px solid #f59e0b;background:#fffbeb;color:#92400e;font-weight:600;cursor:pointer;transition:all .15s;">Nie</button>
            </div>
        </div>

        <div style="margin-bottom:28px;">
            <div style="font-size:0.85rem;color:#94a3b8;margin-bottom:10px;">Zobraziť číslovanie (1., 2., R:)</div>
            <div style="display:flex;gap:8px;">
                <button id="proj-lbl-yes" onclick="setProjShowLabels(true)"
                        style="flex:1;padding:9px;border-radius:8px;border:2px solid #f59e0b;background:#fffbeb;color:#92400e;font-weight:600;cursor:pointer;transition:all .15s;">Áno</button>
                <button id="proj-lbl-no" onclick="setProjShowLabels(false)"
                        style="flex:1;padding:9px;border-radius:8px;border:2px solid transparent;background:#334155;color:#94a3b8;font-weight:600;cursor:pointer;transition:all .15s;">Nie</button>
            </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="closeProjSettings()" style="padding:9px 22px;border-radius:8px;background:#334155;border:none;color:#94a3b8;cursor:pointer;font-size:0.9rem;">Zrušiť</button>
            <button onclick="startProjection()" style="padding:9px 26px;border-radius:8px;background:#f59e0b;border:none;color:#1e293b;font-weight:700;cursor:pointer;font-size:0.9rem;display:inline-flex;align-items:center;gap:7px;"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>Spustiť</button>
        </div>
    </div>
</div>

{{-- ═══════════════ PROJECTION FULLSCREEN ═══════════════ --}}
<style>
#proj-content { column-count: 2; column-gap: 80px; padding: 12px 60px 20px; }
@media (max-width: 700px) {
    #proj-content { column-count: 1; column-gap: 0; padding: 12px 20px 20px; }
}
</style>
<div id="proj-screen" style="display:none;position:fixed;inset:0;background:#000;z-index:9999;overflow:hidden;font-family:'Segoe UI',system-ui,-apple-system,sans-serif;">
    <div style="position:sticky;top:0;display:flex;align-items:center;justify-content:space-between;padding:9px 20px;z-index:1;">
        <div id="proj-title" style="font-size:0.85rem;font-weight:500;color:rgba(255,255,255,0.35);letter-spacing:0.03em;"></div>
        <div style="display:flex;align-items:center;gap:12px;">
            <span id="proj-counter" style="font-size:0.7rem;color:rgba(255,255,255,0.2);font-family:monospace;"></span>
            <button onclick="closeProjection()"
                    style="background:none;border:none;color:rgba(255,255,255,0.18);font-size:1rem;cursor:pointer;line-height:1;padding:4px 8px;border-radius:4px;"
                    onmouseover="this.style.color='rgba(255,255,255,0.6)'"
                    onmouseout="this.style.color='rgba(255,255,255,0.18)'">✕</button>
        </div>
    </div>
    <div id="proj-content" style="height:calc(100vh - 42px);color:#fff;line-height:1.5;overflow:hidden;column-fill:auto;box-sizing:border-box;"></div>
</div>

<script>
const PROJ_SONGS = @json($projectionSongs);
let projIndex = 0;
let projShowChords = localStorage.getItem('projChords') !== '0';
let projSkipRefrain = localStorage.getItem('projSkipRefrain') === '1';
let projShowLabels = localStorage.getItem('projShowLabels') !== '0';

function openProjSettings() {
    const savedFs = localStorage.getItem('projFontSize') || '26';
    document.getElementById('proj-font-range').value = savedFs;
    document.getElementById('proj-font-val').textContent = savedFs + 'px';
    projShowChords = localStorage.getItem('projChords') !== '0';
    projSkipRefrain = localStorage.getItem('projSkipRefrain') === '1';
    projShowLabels  = localStorage.getItem('projShowLabels') !== '0';
    updateProjChordBtns();
    updateProjSkipBtns();
    updateProjLabelBtns();
    document.getElementById('proj-settings').style.display = 'flex';
}

function closeProjSettings() {
    document.getElementById('proj-settings').style.display = 'none';
}

function projFontAdj(delta) {
    const sl = document.getElementById('proj-font-range');
    sl.value = Math.min(48, Math.max(18, parseInt(sl.value) + delta));
    document.getElementById('proj-font-val').textContent = sl.value + 'px';
}

function setProjChords(val) {
    projShowChords = val;
    updateProjChordBtns();
}

function updateProjChordBtns() {
    const yes = document.getElementById('proj-ch-yes');
    const no  = document.getElementById('proj-ch-no');
    if (projShowChords) {
        yes.style.cssText += ';border-color:#f59e0b;background:#fffbeb;color:#92400e;';
        no.style.cssText  += ';border-color:transparent;background:#334155;color:#94a3b8;';
    } else {
        no.style.cssText  += ';border-color:#f59e0b;background:#fffbeb;color:#92400e;';
        yes.style.cssText += ';border-color:transparent;background:#334155;color:#94a3b8;';
    }
}

function setProjSkipRefrain(val) { projSkipRefrain = val; updateProjSkipBtns(); }
function updateProjSkipBtns() {
    var yes = document.getElementById('proj-skip-yes');
    var no  = document.getElementById('proj-skip-no');
    if (projSkipRefrain) {
        yes.style.cssText += ';border-color:#f59e0b;background:#fffbeb;color:#92400e;';
        no.style.cssText  += ';border-color:transparent;background:#334155;color:#94a3b8;';
    } else {
        no.style.cssText  += ';border-color:#f59e0b;background:#fffbeb;color:#92400e;';
        yes.style.cssText += ';border-color:transparent;background:#334155;color:#94a3b8;';
    }
}

function setProjShowLabels(val) { projShowLabels = val; updateProjLabelBtns(); }
function updateProjLabelBtns() {
    var yes = document.getElementById('proj-lbl-yes');
    var no  = document.getElementById('proj-lbl-no');
    if (projShowLabels) {
        yes.style.cssText += ';border-color:#f59e0b;background:#fffbeb;color:#92400e;';
        no.style.cssText  += ';border-color:transparent;background:#334155;color:#94a3b8;';
    } else {
        no.style.cssText  += ';border-color:#f59e0b;background:#fffbeb;color:#92400e;';
        yes.style.cssText += ';border-color:transparent;background:#334155;color:#94a3b8;';
    }
}

var _projTouchStartX = null;

function projTouchStart(e) {
    _projTouchStartX = e.touches[0].clientX;
}
function projTouchEnd(e) {
    if (_projTouchStartX === null) return;
    var dx = e.changedTouches[0].clientX - _projTouchStartX;
    _projTouchStartX = null;
    if (Math.abs(dx) < 50) return;
    projNavigate(dx < 0 ? 1 : -1);
}

function startProjection() {
    localStorage.setItem('projFontSize', document.getElementById('proj-font-range').value);
    localStorage.setItem('projChords', projShowChords ? '1' : '0');
    localStorage.setItem('projSkipRefrain', projSkipRefrain ? '1' : '0');
    localStorage.setItem('projShowLabels', projShowLabels ? '1' : '0');
    closeProjSettings();
    projIndex = 0;
    var screen = document.getElementById('proj-screen');
    screen.style.display = 'block';
    projRender();
    document.addEventListener('keydown', projKeyHandler);
    screen.addEventListener('touchstart', projTouchStart, { passive: true });
    screen.addEventListener('touchend', projTouchEnd, { passive: true });
    var el = screen;
    var req = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
    if (req) req.call(el).catch(function(){});
}

function closeProjection() {
    var screen = document.getElementById('proj-screen');
    screen.style.display = 'none';
    document.removeEventListener('keydown', projKeyHandler);
    screen.removeEventListener('touchstart', projTouchStart);
    screen.removeEventListener('touchend', projTouchEnd);
    var exit = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
    if (exit && (document.fullscreenElement || document.webkitFullscreenElement)) exit.call(document).catch(function(){});
}

document.addEventListener('fullscreenchange', function() {
    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
        var screen = document.getElementById('proj-screen');
        if (screen && screen.style.display !== 'none') closeProjection();
    }
});
document.addEventListener('webkitfullscreenchange', function() {
    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
        var screen = document.getElementById('proj-screen');
        if (screen && screen.style.display !== 'none') closeProjection();
    }
});

function projKeyHandler(e) {
    if (e.key === 'ArrowRight' || e.key === 'PageDown') { e.preventDefault(); projNavigate(1); }
    if (e.key === 'ArrowLeft'  || e.key === 'PageUp')   { e.preventDefault(); projNavigate(-1); }
    if (e.key === 'Escape') closeProjection();
}

function projNavigate(delta) {
    projIndex = Math.max(0, Math.min(PROJ_SONGS.length - 1, projIndex + delta));
    projRender();
}

function projEsc(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function projParseLine(text, showChords) {
    return text.split(/(<[A-H][^>]{0,20}>)/).map(function(part, i) {
        if (i % 2 === 1) {
            if (!showChords) return '';
            var ch = part.slice(1,-1).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            return '<sup style="color:#fbbf24;font-size:0.6em;font-weight:700;letter-spacing:0.03em;">' + ch + '</sup>';
        }
        return projEsc(part);
    }).join('');
}

function projSplitSections(text) {
    var sections = [];
    var current = { type: 'text', lines: [] };
    text.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n').forEach(function(line) {
        var t = line.trim();
        if (t === '[SLOHA]') {
            if (current.lines.some(function(l) { return l.trim(); })) sections.push(current);
            current = { type: 'sloha', lines: [] };
        } else if (t === '[REFRÉN]') {
            if (current.lines.some(function(l) { return l.trim(); })) sections.push(current);
            current = { type: 'refren', lines: [] };
        } else {
            current.lines.push(line);
        }
    });
    if (current.lines.some(function(l) { return l.trim(); })) sections.push(current);
    return sections;
}

function projBuildHTML(song) {
    var sections = projSplitSections(song.lyrics || '');
    if (projSkipRefrain) {
        sections = sections.filter(function(s) { return s.type !== 'refren'; });
    }
    var sloka = 0;
    return sections.map(function(s) {
        var label = '';
        if (s.type === 'sloha') { sloka++; if (projShowLabels) label = sloka + '.'; }
        else if (s.type === 'refren') { if (projShowLabels) label = 'R:'; }
        var sLines = s.lines.join('\n').trim().split('\n').filter(function(l) { return l.trim(); });
        if (!sLines.length) return '';
        var textHtml = sLines.map(function(l) { return projParseLine(l, projShowChords); }).join('<br>');
        if (label) {
            return '<div style="display:flex;gap:0.25em;margin-bottom:1.3em;align-items:baseline;break-inside:avoid;page-break-inside:avoid;">'
                + '<span style="flex-shrink:0;">' + projEsc(label) + '</span>'
                + '<span>' + textHtml + '</span>'
                + '</div>';
        }
        return '<div style="margin-bottom:1.3em;break-inside:avoid;page-break-inside:avoid;">' + textHtml + '</div>';
    }).filter(Boolean).join('');
}

function projRender() {
    var song = PROJ_SONGS[projIndex];
    var prefFs = parseInt(localStorage.getItem('projFontSize') || '26');
    var content = document.getElementById('proj-content');
    document.getElementById('proj-title').textContent = song.name;
    document.getElementById('proj-counter').textContent = (projIndex + 1) + ' / ' + PROJ_SONGS.length;
    content.innerHTML = projBuildHTML(song);
    content.style.fontSize = prefFs + 'px';
    requestAnimationFrame(function() {
        var size = prefFs;
        while ((content.scrollWidth > content.clientWidth + 2) && size > 10) {
            size -= 0.5;
            content.style.fontSize = size + 'px';
        }
    });
}
</script>
@endsection
