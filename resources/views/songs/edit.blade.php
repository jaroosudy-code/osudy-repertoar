@extends('layouts.app')
@section('title', 'Upraviť pieseň')

@section('content')
<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('songs.index') }}" class="text-slate-400 hover:text-slate-600">← Späť</a>
        <h1 class="text-2xl font-bold text-slate-800">Upraviť pieseň</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="POST" action="{{ route('songs.update', $song) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Názov piesne</label>
                <input type="text" name="name" value="{{ old('name', $song->name) }}" required autofocus
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dĺžka *</label>
                <input type="text" name="duration_formatted" value="{{ old('duration_formatted', $song->duration_formatted) }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono"
                       placeholder="3.45  alebo  3,45  alebo  3:45">
                <p class="text-xs text-slate-400 mt-1">Minúty a sekundy oddeľ bodkou, čiarkou alebo dvojbodkou</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Farba</label>
                @include('songs._color_palette', ['currentColor' => old('color', $song->color)])
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Tempo *</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tempo" value="fast" {{ old('tempo', $song->tempo) === 'fast' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <span class="px-2 py-0.5 rounded-full text-sm bg-blue-100 text-blue-700">Rýchla</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tempo" value="slow" {{ old('tempo', $song->tempo) === 'slow' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <span class="px-2 py-0.5 rounded-full text-sm bg-orange-100 text-orange-700">Pomalá</span>
                    </label>
                </div>
            </div>

            <div x-data="{ typ: '{{ old('type', $song->type) }}' }">
                <label class="block text-sm font-medium text-slate-700 mb-2">Typ *</label>
                <div class="flex gap-4 mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="own" x-model="typ"
                               {{ old('type', $song->type) === 'own' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <span class="px-2 py-0.5 rounded-full text-sm bg-green-100 text-green-700">Osudy</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="cover" x-model="typ"
                               {{ old('type', $song->type) === 'cover' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <span class="px-2 py-0.5 rounded-full text-sm bg-purple-100 text-purple-700">Cover</span>
                    </label>
                </div>
                <div x-show="typ === 'cover'" x-transition>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Interpret (original)</label>
                    <input type="text" name="original_artist" value="{{ old('original_artist', $song->original_artist) }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                           placeholder="napr. Eagles, ABBA, Karel Gott...">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">BPM</label>
                <input type="number" name="bpm" value="{{ old('bpm', $song->bpm) }}" min="20" max="300"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono"
                       placeholder="napr. 120">
            </div>
            </div>{{-- /grid tempo+typ+bpm --}}

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Autor textu</label>
                    <input type="text" name="author_lyrics" value="{{ old('author_lyrics', $song->author_lyrics) }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Autor hudby</label>
                    <input type="text" name="author_music" value="{{ old('author_music', $song->author_music) }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>

            <div>
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <label class="block text-sm font-medium text-slate-700">Text s akordmi</label>
                    <button type="button" id="btn-chord"
                            style="font-family:monospace;font-size:0.8rem;padding:2px 10px;border:1.5px solid #cbd5e1;border-radius:6px;background:#f8fafc;color:#64748b;cursor:pointer;font-weight:700;transition:all 0.15s;line-height:1.8;"
                            onclick="toggleChordInsert()">&lt;CHORD&gt;</button>
                    <button type="button"
                            style="font-family:monospace;font-size:0.8rem;padding:2px 10px;border:1.5px solid #86efac;border-radius:6px;background:#f0fdf4;color:#16a34a;cursor:pointer;font-weight:700;line-height:1.8;"
                            onclick="insertSection('[SLOHA]')">[SLOHA]</button>
                    <button type="button"
                            style="font-family:monospace;font-size:0.8rem;padding:2px 10px;border:1.5px solid #f9a8d4;border-radius:6px;background:#fdf2f8;color:#db2777;cursor:pointer;font-weight:700;line-height:1.8;"
                            onclick="insertSection('[REFRÉN]')">[REFRÉN]</button>
                </div>
                <p class="text-xs text-slate-400 mb-2">Klikni <strong>&lt;CHORD&gt;</strong> → napíš akord → klikni znova pre uzatvorenie &nbsp;|&nbsp; <strong>[SLOHA]</strong> a <strong>[REFRÉN]</strong> označujú sekcie pre premietanie</p>
                <div style="position:relative;" id="lyrics-ta-wrapper">
                    <div id="lyrics-highlight" aria-hidden="true"
                         style="position:absolute;top:1px;left:1px;right:17px;
                                font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;
                                font-size:0.875rem;line-height:1.25rem;
                                padding:8px 12px;white-space:pre-wrap;word-break:break-word;
                                overflow:hidden;pointer-events:none;
                                background:white;color:#1e293b;border-radius:7px;"></div>
                    <textarea name="lyrics" id="lyrics-ta" rows="12"
                              style="position:relative;z-index:1;background:transparent;color:transparent;caret-color:#1e293b;display:block;"
                              class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono text-sm">{{ old('lyrics', $song->lyrics) }}</textarea>
                    <div id="lyrics-resize-handle" aria-hidden="true"
                         style="display:none;position:absolute;bottom:3px;right:3px;width:28px;height:28px;
                                z-index:5;touch-action:none;border-bottom-right-radius:5px;
                                background:linear-gradient(135deg,transparent 50%,rgba(148,163,184,0.65) 50%);"></div>
                </div>
<style>#lyrics-highlight::-webkit-scrollbar{display:none;}</style>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-5 py-2 rounded-lg transition-colors">
                    Uložiť zmeny
                </button>
                <a href="{{ route('songs.show', $song) }}"
                   class="px-5 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                    Zrušiť
                </a>
            </div>
        </form>
    </div>
</div>
<script>
var chordInserting = false;
var isTouchDevice = ('ontouchstart' in window || navigator.maxTouchPoints > 0);

function escHtml(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function processChordLine(line) {
    var html = '';
    var i = 0;
    while (i < line.length) {
        if (line[i] !== '<') {
            var j = line.indexOf('<', i);
            html += escHtml(j === -1 ? line.slice(i) : line.slice(i, j));
            i = j === -1 ? line.length : j;
        } else {
            var end = -1;
            for (var k = i + 1; k < line.length; k++) {
                if (line[k] === '>') { end = k; break; }
                if (line[k] === '<') break;
            }
            if (end !== -1) {
                html += '<span style="color:#d97706;font-weight:700;">' + escHtml(line.slice(i, end + 1)) + '</span>';
                i = end + 1;
            } else {
                var stop = line.length;
                for (var m = i + 1; m < line.length; m++) {
                    if (line[m] === '<') { stop = m; break; }
                }
                html += '<span style="color:#ef4444;">' + escHtml(line.slice(i, stop)) + '</span>';
                i = stop;
            }
        }
    }
    return html;
}

function updateHighlight() {
    var ta = document.getElementById('lyrics-ta');
    var hl = document.getElementById('lyrics-highlight');
    if (!ta || !hl) return;
    var html = ta.value.split('\n').map(function(line) {
        var t = line.trim();
        if (t === '[SLOHA]')  return '<span style="background:#dcfce7;color:#16a34a;font-weight:700;border-radius:3px;padding:0 3px;">[SLOHA]</span>';
        if (t === '[REFRÉN]') return '<span style="background:#fce7f3;color:#db2777;font-weight:700;border-radius:3px;padding:0 3px;">[REFRÉN]</span>';
        return processChordLine(line);
    }).join('\n');
    hl.innerHTML = html;
    hl.scrollTop = ta.scrollTop;
}

function insertSection(marker) {
    var ta = document.getElementById('lyrics-ta');
    var pos = ta.selectionStart;
    var v = ta.value;
    var before = v.slice(0, pos);
    var after = v.slice(pos);
    var prefix = (before.length > 0 && before[before.length - 1] !== '\n') ? '\n' : '';
    var suffix = (after.length > 0 && after[0] !== '\n') ? '\n' : '';
    var insert = prefix + marker + suffix;
    ta.value = before + insert + after;
    ta.selectionStart = ta.selectionEnd = pos + insert.length;
    ta.focus();
    updateHighlight();
}

function toggleChordInsert() {
    var btn = document.getElementById('btn-chord');
    var ta = document.getElementById('lyrics-ta');
    var pos = ta.selectionStart;
    var v = ta.value;
    if (!chordInserting) {
        ta.value = v.slice(0, pos) + '<' + v.slice(pos);
        ta.selectionStart = ta.selectionEnd = pos + 1;
        chordInserting = true;
        btn.style.borderColor = '#f59e0b';
        btn.style.color = '#f59e0b';
        btn.style.background = '#fffbeb';
    } else {
        ta.value = v.slice(0, pos) + '>' + v.slice(pos);
        ta.selectionStart = ta.selectionEnd = pos + 1;
        chordInserting = false;
        btn.style.borderColor = '#cbd5e1';
        btn.style.color = '#64748b';
        btn.style.background = '#f8fafc';
    }
    ta.focus();
    updateHighlight();
}

function hasUnclosedChord(text) {
    var parts = text.split('<');
    for (var i = 1; i < parts.length; i++) {
        if (parts[i].indexOf('>') === -1) return true;
    }
    return false;
}

(function() {
    var ta = document.getElementById('lyrics-ta');
    var hl = document.getElementById('lyrics-highlight');
    function syncHlHeight() { hl.style.height = ta.clientHeight + 'px'; }
    syncHlHeight();
    if (typeof ResizeObserver !== 'undefined') { new ResizeObserver(syncHlHeight).observe(ta); }
    ta.addEventListener('input', updateHighlight);
    ta.addEventListener('scroll', function() { hl.scrollTop = ta.scrollTop; });
    updateHighlight();

    if (isTouchDevice) {
        ta.style.overflow = 'auto';
        ta.style.resize = 'none';
        hl.style.right = '1px';

        var rh = document.getElementById('lyrics-resize-handle');
        if (rh) {
            rh.style.display = 'block';
            rh.addEventListener('touchstart', function(e) {
                var startY = e.touches[0].clientY;
                var startH = ta.offsetHeight;
                e.preventDefault();
                function onMove(ev) {
                    ta.style.height = Math.max(120, startH + ev.touches[0].clientY - startY) + 'px';
                    ev.preventDefault();
                }
                function onEnd() {
                    document.removeEventListener('touchmove', onMove);
                    document.removeEventListener('touchend', onEnd);
                }
                document.addEventListener('touchmove', onMove, {passive: false});
                document.addEventListener('touchend', onEnd);
            }, {passive: false});
        }
    }

    ta.closest('form').addEventListener('submit', function(e) {
        if (chordInserting || hasUnclosedChord(ta.value)) {
            e.preventDefault();
            alert('Akord nie je uzavretý! Uzatvor ho pred uložením.');
            document.getElementById('btn-chord').style.boxShadow = '0 0 0 3px rgba(239,68,68,0.4)';
            setTimeout(function() { document.getElementById('btn-chord').style.boxShadow = ''; }, 1500);
        }
    });
})();
</script>
@endsection
