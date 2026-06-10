@extends('layouts.app')
@section('title', $song->name)

@section('content')
<style>
.chord-span {
    color: #fbbf24;
    font-weight: bold;
    font-size: 0.88em;
    vertical-align: 0.45em;
    line-height: 0;
    cursor: pointer;
    text-decoration: underline dotted;
    text-underline-offset: 2px;
}
.chord-span:hover { color: #f59e0b; }

/* ── Chord popup ─────────────────────────────────────────── */
#chord-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 100;
    align-items: center;
    justify-content: center;
}
#chord-overlay.open { display: flex; }

#chord-popup {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 40px rgba(0,0,0,.35);
    padding: 20px 24px 16px;
    min-width: 230px;
    max-width: 94vw;
    position: relative;
}
#chord-popup-title {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    color: #c0392b;
    margin-bottom: 12px;
}
#chord-close {
    position: absolute; top: 10px; right: 14px;
    background: none; border: none; font-size: 1.3rem;
    cursor: pointer; color: #94a3b8;
    line-height: 1;
}
#chord-close:hover { color: #475569; }

#chord-diagram-wrap { display: flex; justify-content: center; }

#chord-actions {
    display: flex; justify-content: center; gap: 10px;
    margin-top: 12px;
}
#chord-actions button, .save-btn {
    padding: 5px 14px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #475569;
    font-size: 0.82rem;
    cursor: pointer;
    width: 100%;
    text-align: center;
}
#chord-actions button:hover, .save-btn:hover { background: #e2e8f0; }
#chord-save-options { margin-top: 10px; }
.save-btn-local  { background: #eff6ff !important; border-color: #93c5fd !important; color: #1d4ed8 !important; }
.save-btn-local:hover  { background: #dbeafe !important; }
.save-btn-global { background: #0f172a !important; color: #fff !important; border-color: #0f172a !important; }
.save-btn-global:hover { background: #1e293b !important; }
.save-btn-discard { color: #94a3b8 !important; }

#chord-editor-controls {
    margin-top: 10px;
    font-size: 0.8rem;
    color: #64748b;
}
#chord-editor-controls label { display: block; margin-bottom: 4px; }
#chord-editor-controls input[type=number] {
    width: 56px; padding: 3px 6px; border: 1px solid #cbd5e1;
    border-radius: 6px; font-size: 0.85rem;
}
#chord-editor-hint {
    font-size: 0.75rem; color: #94a3b8; text-align: center;
    margin-top: 6px;
}

@media print {
    @page { size: A4; margin: 12mm 15mm; }
    nav, .no-print { display: none !important; }
    body { background: white !important; }
    main { padding: 0 !important; }
    .print-header {
        display: flex !important;
        justify-content: space-between;
        align-items: baseline;
        border-bottom: 1px solid #ccc;
        padding-bottom: 6pt;
        margin-bottom: 10pt;
    }
    .print-header-left  { font-size: 9pt; color: #555; }
    .print-header-title { font-size: 15pt; font-weight: bold; text-align: center; flex: 1; }
    .print-header-right { font-size: 9pt; color: #555; text-align: right; }
    .lyrics-screen-wrapper { background: white !important; border-radius: 0 !important; padding: 0 !important; }
    #lyrics-container { color: black !important; font-size: 9.5pt !important; line-height: 2.1em !important; }
    .chord-span { color: #92400e !important; cursor: default; text-decoration: none; }
    #chord-overlay { display: none !important; }
}
</style>

{{-- Print header --}}
<div class="print-header" style="display:none;">
    <span class="print-header-left">
        {{ $song->type === 'own' ? 'Osudy' : ($song->original_artist ?? 'Cover') }}
    </span>
    <span class="print-header-title">{{ $song->name }}</span>
    <span class="print-header-right">
        @if($song->author_lyrics) Text: {{ $song->author_lyrics }}<br>@endif
        @if($song->author_music) Hudba: {{ $song->author_music }}@endif
    </span>
</div>

{{-- Chord popup --}}
<div id="chord-overlay" onclick="if(event.target===this)closeChordPopup()">
    <div id="chord-popup">
        <button id="chord-close" onclick="closeChordPopup()">✕</button>
        <div id="chord-popup-title">—</div>
        <div id="chord-diagram-wrap"></div>
        <div id="chord-editor-controls" style="display:none;">
            <label>Začiatočný pražec:
                <input type="number" id="inp-starting-fret" min="1" max="20" value="1"
                       oninput="onStartingFretChange(this.value)">
            </label>
            <div id="chord-editor-hint">Klikni na mriežku → nastaví prst 1–4 (ďalší klik vymaže)</div>
        </div>
        <div id="chord-actions" class="no-print">
            <button id="btn-edit-chord" onclick="toggleChordEdit()">Upraviť</button>
        </div>
        <div id="chord-save-options" class="no-print" style="display:none;">
            <div style="font-size:0.75rem;color:#94a3b8;text-align:center;margin-bottom:6px;">Uložiť zmeny:</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <button onclick="saveChord('local')"  class="save-btn save-btn-local">Iba pre túto pieseň</button>
                <button onclick="saveChord('global')" class="save-btn save-btn-global">Pre celú databázu</button>
                <button onclick="discardChordEdit()"  class="save-btn save-btn-discard">Zrušiť</button>
            </div>
        </div>
    </div>
</div>

<div>
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-4 no-print">
        <a href="{{ route('songs.index') }}" class="text-slate-400 hover:text-slate-600">← Späť</a>
        <h1 class="text-2xl font-bold text-slate-800 flex-1">{{ $song->name }}</h1>
        @if($song->lyrics)
        <button onclick="window.print()"
                style="padding:6px 16px; border-radius:8px; background:#0f172a; color:white; border:none; font-size:0.875rem; cursor:pointer;">
            🖨 Tlačiť
        </button>
        @endif
        <a href="{{ route('songs.edit', $song) }}"
           class="px-4 py-1.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 text-sm transition-colors">
            Upraviť
        </a>
    </div>

    {{-- Info lišta --}}
    <div class="flex flex-wrap gap-3 mb-6 text-sm text-slate-500 no-print">
        <span class="font-mono font-medium text-slate-700">{{ $song->duration_formatted }}</span>
        @if($song->tempo === 'fast')
            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Rýchla</span>
        @else
            <span class="px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">Pomalá</span>
        @endif
        @if($song->type === 'own')
            <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700">Osudy</span>
        @else
            <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">Cover</span>
            @if($song->original_artist)
                <span class="text-slate-400">{{ $song->original_artist }}</span>
            @endif
        @endif
        @if($song->author_lyrics)
            <span>Text: {{ $song->author_lyrics }}</span>
        @endif
        @if($song->author_music)
            <span>Hudba: {{ $song->author_music }}</span>
        @endif
    </div>

    @if($song->lyrics)
    {{-- Transpozícia --}}
    <div class="no-print" style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
        <span style="font-size:0.875rem; font-weight:500; color:#475569;">Transpozícia:</span>
        <button onclick="transpose(-1)"
                style="width:32px;height:32px;border-radius:50%;background:#e2e8f0;border:none;font-size:1.1rem;font-weight:bold;color:#334155;cursor:pointer;">−</button>
        <span id="offset-display" style="font-size:0.875rem;font-family:monospace;color:#475569;min-width:80px;text-align:center;">0 (originál)</span>
        <button onclick="transpose(+1)"
                style="width:32px;height:32px;border-radius:50%;background:#e2e8f0;border:none;font-size:1.1rem;font-weight:bold;color:#334155;cursor:pointer;">+</button>
    </div>

    {{-- Text s akordmi --}}
    <div class="lyrics-screen-wrapper" style="background:#0f172a; border-radius:12px; padding:24px;">
        <div id="lyrics-container"
             style="font-family:monospace; font-size:1rem; line-height:2.4em; white-space:pre-wrap; color:#e2e8f0;">
@php
    $raw = $song->lyrics ?? '';
    $parts = preg_split('/(<[A-H][^>]{0,20}>)/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $i => $part) {
        if ($i % 2 === 1) {
            $chord = htmlspecialchars(substr($part, 1, -1));
            echo '<span class="chord-span" onclick="showChordPopup(\'' . addslashes($chord) . '\')">' . $chord . '</span>';
        } else {
            echo htmlspecialchars($part);
        }
    }
@endphp
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400">
        <p>Táto pieseň zatiaľ nemá text s akordmi.</p>
        <a href="{{ route('songs.edit', $song) }}" class="text-amber-500 hover:underline mt-2 inline-block">Pridať text →</a>
    </div>
    @endif

    @if($song->notes)
    <div class="mt-4 bg-white rounded-xl border border-slate-200 p-4 text-sm text-slate-600 no-print">
        <span class="font-medium text-slate-700">Poznámky:</span> {{ $song->notes }}
    </div>
    @endif
</div>

@if($song->lyrics)
<script>
const SCALE = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','H'];
let currentOffset = 0;
const rawLyrics = @json($song->lyrics ?? '');
const SONG_ID = {{ $song->id }};

// ── Transpozícia ─────────────────────────────────────────────────────────────
function transposeChord(chord, semitones) {
    const m = chord.match(/^([A-H][#b]?)(.*)$/);
    if (!m) return chord;
    let idx = SCALE.indexOf(m[1]);
    if (idx === -1) return chord;
    idx = ((idx + semitones) % 12 + 12) % 12;
    return SCALE[idx] + m[2];
}

function renderLyrics(text, semitones) {
    const parts = text.split(/(<[A-H][^>]{0,20}>)/);
    return parts.map((part, i) => {
        if (i % 2 === 1) {
            const chord = part.slice(1, -1);
            const transposed = transposeChord(chord, semitones);
            const safe = transposed.replace(/'/g, "\\'");
            return `<span class="chord-span" onclick="showChordPopup('${safe}')">${transposed}</span>`;
        } else {
            return part.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
    }).join('');
}

function transpose(delta) {
    currentOffset = currentOffset + delta;
    if (currentOffset > 6)  currentOffset -= 12;
    if (currentOffset < -6) currentOffset += 12;
    const display = document.getElementById('offset-display');
    display.textContent = currentOffset === 0 ? '0 (originál)' : (currentOffset > 0 ? '+' : '') + currentOffset;
    document.getElementById('lyrics-container').innerHTML = renderLyrics(rawLyrics, currentOffset);
}

// ── Chord popup ───────────────────────────────────────────────────────────────
let activeChordName = '';
let editMode = false;
let editState = { frets: [], fingers: [], starting_fret: 1, barre_fret: null, barre_from_string: null, barre_to_string: null };

function showChordPopup(chordName) {
    activeChordName = chordName;
    setEditMode(false);
    document.getElementById('chord-popup-title').textContent = chordName;

    fetch('/api/chords?name=' + encodeURIComponent(chordName) + '&song_id=' + SONG_ID, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data) {
            editState = {
                frets: [...data.frets],
                fingers: [...data.fingers],
                starting_fret: data.starting_fret ?? 1,
                barre_fret: data.barre_fret ?? null,
                barre_from_string: data.barre_from_string ?? null,
                barre_to_string: data.barre_to_string ?? null,
            };
        } else {
            editState = { frets: [-1,-1,-1,-1,-1,-1], fingers: [0,0,0,0,0,0], starting_fret: 1,
                          barre_fret: null, barre_from_string: null, barre_to_string: null };
        }
        renderDiagram(false);
        document.getElementById('chord-overlay').classList.add('open');
    });
}

function closeChordPopup() {
    document.getElementById('chord-overlay').classList.remove('open');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeChordPopup(); });

// ── SVG rendering ─────────────────────────────────────────────────────────────
const SVG_W = 200, SVG_H = 230;
const ST_X = [30, 56, 82, 108, 134, 160];
const FRET_Y = [58, 84, 110, 136, 162, 188];
const NOTE_Y = [71, 97, 123, 149, 175];
const OX_Y = 40;
const LABEL_Y = 207;
const STRING_LABELS = ['E','A','D','G','H','e'];
const FRET_SLOTS = 5;

function renderDiagram(editing) {
    const { frets, fingers, starting_fret, barre_fret, barre_from_string, barre_to_string } = editState;
    let svg = `<svg width="${SVG_W}" height="${SVG_H}" viewBox="0 0 ${SVG_W} ${SVG_H}" xmlns="http://www.w3.org/2000/svg">`;

    if (starting_fret === 1) {
        svg += `<rect x="${ST_X[0]}" y="${FRET_Y[0]-4}" width="${ST_X[5]-ST_X[0]}" height="5" fill="#222" rx="2"/>`;
    } else {
        svg += `<line x1="${ST_X[0]}" y1="${FRET_Y[0]}" x2="${ST_X[5]}" y2="${FRET_Y[0]}" stroke="#999" stroke-width="1.5"/>`;
        svg += `<text x="${ST_X[0]-6}" y="${FRET_Y[0]+4}" text-anchor="end" font-size="11" fill="#555">${starting_fret}</text>`;
    }

    for (let f = 1; f < FRET_Y.length; f++) {
        svg += `<line x1="${ST_X[0]}" y1="${FRET_Y[f]}" x2="${ST_X[5]}" y2="${FRET_Y[f]}" stroke="#ccc" stroke-width="1"/>`;
    }
    for (let s = 0; s < 6; s++) {
        svg += `<line x1="${ST_X[s]}" y1="${FRET_Y[0]}" x2="${ST_X[s]}" y2="${FRET_Y[FRET_Y.length-1]}" stroke="#ccc" stroke-width="1"/>`;
    }

    if (editing) {
        for (let s = 0; s < 6; s++) {
            for (let slot = 0; slot < FRET_SLOTS; slot++) {
                svg += `<circle cx="${ST_X[s]}" cy="${NOTE_Y[slot]}" r="12" fill="transparent" style="cursor:pointer"
                    onclick="editFretClick(${s},${slot})"/>`;
            }
        }
    }

    if (barre_fret !== null && barre_from_string !== null && barre_to_string !== null) {
        const slot = barre_fret - starting_fret;
        if (slot >= 0 && slot < FRET_SLOTS) {
            svg += `<line x1="${ST_X[barre_from_string]}" y1="${NOTE_Y[slot]}" x2="${ST_X[barre_to_string]}" y2="${NOTE_Y[slot]}"
                stroke="#222" stroke-width="14" stroke-linecap="round" opacity="0.85"/>`;
        }
    }

    for (let s = 0; s < 6; s++) {
        const fret = frets[s];
        if (fret <= 0) continue;
        const slot = fret - starting_fret;
        if (slot < 0 || slot >= FRET_SLOTS) continue;
        svg += `<circle cx="${ST_X[s]}" cy="${NOTE_Y[slot]}" r="11" fill="#222"/>`;
        if (fingers[s] > 0) {
            svg += `<text x="${ST_X[s]}" y="${NOTE_Y[slot]+4}" text-anchor="middle" font-size="11" font-weight="bold" fill="#fff">${fingers[s]}</text>`;
        }
    }

    for (let s = 0; s < 6; s++) {
        const fret = frets[s];
        const label = fret === -1 ? '✕' : '○';
        const col   = fret === -1 ? '#e74c3c' : '#27ae60';
        const click = editing ? ` style="cursor:pointer" onclick="editOpenMuteClick(${s})"` : '';
        svg += `<text x="${ST_X[s]}" y="${OX_Y}" text-anchor="middle" font-size="13" fill="${col}"${click}>${label}</text>`;
    }

    for (let s = 0; s < 6; s++) {
        svg += `<text x="${ST_X[s]}" y="${LABEL_Y}" text-anchor="middle" font-size="11" fill="#94a3b8">${STRING_LABELS[s]}</text>`;
    }

    svg += '</svg>';
    document.getElementById('chord-diagram-wrap').innerHTML = svg;
}

// ── Editor interactions ───────────────────────────────────────────────────────
function setEditMode(on) {
    editMode = on;
    document.getElementById('btn-edit-chord').textContent = on ? 'Späť' : 'Upraviť';
    document.getElementById('chord-save-options').style.display = on ? '' : 'none';
    document.getElementById('chord-actions').style.display = on ? 'none' : '';
    document.getElementById('chord-editor-controls').style.display = on ? '' : 'none';
    if (on) document.getElementById('inp-starting-fret').value = editState.starting_fret;
}

function toggleChordEdit() {
    setEditMode(!editMode);
    renderDiagram(editMode);
}

function discardChordEdit() {
    // Reload original diagram from server
    showChordPopup(activeChordName);
}

function onStartingFretChange(val) {
    const sf = Math.max(1, Math.min(20, parseInt(val) || 1));
    editState.starting_fret = sf;
    editState.barre_fret = null;
    editState.barre_from_string = null;
    editState.barre_to_string = null;
    renderDiagram(true);
}

function editFretClick(stringIdx, slot) {
    const absoluteFret = editState.starting_fret + slot;
    const currentFret  = editState.frets[stringIdx];
    const currentFinger = editState.fingers[stringIdx];

    if (currentFret === absoluteFret) {
        if (currentFinger < 4) {
            editState.fingers[stringIdx] = currentFinger + 1;
        } else {
            editState.frets[stringIdx] = 0;
            editState.fingers[stringIdx] = 0;
        }
    } else {
        editState.frets[stringIdx] = absoluteFret;
        editState.fingers[stringIdx] = nextAvailableFinger();
    }
    detectBarre();
    renderDiagram(true);
}

function editOpenMuteClick(stringIdx) {
    if (editState.frets[stringIdx] === -1) {
        editState.frets[stringIdx] = 0;
    } else {
        editState.frets[stringIdx] = -1;
        editState.fingers[stringIdx] = 0;
    }
    renderDiagram(true);
}

function nextAvailableFinger() {
    const used = new Set(editState.fingers.filter(f => f > 0));
    for (let f = 1; f <= 4; f++) { if (!used.has(f)) return f; }
    return 1;
}

function detectBarre() {
    const f1 = editState.frets
        .map((fret, s) => ({ fret, finger: editState.fingers[s], s }))
        .filter(x => x.finger === 1 && x.fret > 0);

    if (f1.length >= 2 && f1.every(x => x.fret === f1[0].fret)) {
        const indices = f1.map(x => x.s).sort((a,b) => a-b);
        editState.barre_fret = f1[0].fret;
        editState.barre_from_string = indices[0];
        editState.barre_to_string   = indices[indices.length - 1];
    } else {
        editState.barre_fret = editState.barre_from_string = editState.barre_to_string = null;
    }
}

function saveChord(scope) {
    // scope: 'local' (for this song) | 'global' (entire DB)
    const payload = {
        name: activeChordName,
        scope: scope === 'local' ? 'song' : 'global',
        song_id: scope === 'local' ? SONG_ID : null,
        frets: editState.frets,
        fingers: editState.fingers,
        starting_fret: editState.starting_fret,
        barre_fret: editState.barre_fret,
        barre_from_string: editState.barre_from_string,
        barre_to_string: editState.barre_to_string,
    };

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/chords', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(() => setEditMode(false))
    .then(() => renderDiagram(false));
}
</script>
@endif
@endsection
