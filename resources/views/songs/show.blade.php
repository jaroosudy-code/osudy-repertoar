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

    .lyrics-screen-wrapper {
        background: white !important;
        border-radius: 0 !important;
        padding: 0 !important;
    }
    #lyrics-container {
        color: black !important;
        font-size: 9.5pt !important;
        line-height: 2.1em !important;
    }
    .chord-span {
        color: #92400e !important;
    }
}
</style>

{{-- Hlavička len pre tlač --}}
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
            echo '<span class="chord-span">' . $chord . '</span>';
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
const SCALE = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'H'];
let currentOffset = 0;
const rawLyrics = @json($song->lyrics ?? '');

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
            return `<span class="chord-span">${transposed}</span>`;
        } else {
            return part.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
    }).join('');
}

function transpose(delta) {
    currentOffset = currentOffset + delta;
    if (currentOffset > 6) currentOffset = currentOffset - 12;
    if (currentOffset < -6) currentOffset = currentOffset + 12;

    const display = document.getElementById('offset-display');
    display.textContent = currentOffset === 0 ? '0 (originál)' : (currentOffset > 0 ? '+' : '') + currentOffset;

    document.getElementById('lyrics-container').innerHTML = renderLyrics(rawLyrics, currentOffset);
}
</script>
@endif
@endsection
