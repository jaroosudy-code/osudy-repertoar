@extends('layouts.app')
@section('title', 'Metronóm')

@section('content')

<style>
.beat-btn {
    width: 3rem; height: 3rem; border-radius: 0.5rem; border: 2px solid;
    font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: all 0.1s;
    display: flex; align-items: center; justify-content: center;
}
.beat-btn.lvl-2 { background: #f59e0b; border-color: #d97706; color: #1e293b; }
.beat-btn.lvl-1 { background: #e2e8f0; border-color: #94a3b8; color: #475569; }
.beat-btn.lvl-0 { background: transparent; border-color: #e2e8f0; color: #cbd5e1; }
.vdot { width: 1.25rem; height: 1.25rem; border-radius: 50%; transition: transform 0.05s, opacity 0.05s; flex-shrink: 0; }
.vdot.lvl-2 { background: #d97706; opacity: 0.4; }
.vdot.lvl-1 { background: #94a3b8; opacity: 0.3; }
.vdot.lvl-0 { background: #e2e8f0; opacity: 0.2; }
.vdot.lit   { opacity: 1 !important; transform: scale(1.45); }
.sig-btn {
    padding: 0.375rem 0.875rem; border-radius: 0.5rem; border: 2px solid #e2e8f0;
    font-size: 0.875rem; font-family: monospace; font-weight: 600;
    cursor: pointer; transition: all 0.15s; background: transparent; color: #475569;
}
.sig-btn:hover  { border-color: #f59e0b; color: #92400e; }
.sig-btn.active { background: #f59e0b; border-color: #d97706; color: #1e293b; }
.metro-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
.metro-label { font-size: 0.875rem; font-weight: 500; color: #475569; margin-bottom: 0.5rem; }
.metro-h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.bpm-num { font-size: 5rem; line-height: 1; font-weight: 800; font-family: monospace; color: #1e293b; }
.bpm-sub { font-size: 0.8rem; font-weight: 500; letter-spacing: 0.1em; color: #94a3b8; margin-top: 0.125rem; }
.metro-slider { -webkit-appearance: none; appearance: none; width: 100%; height: 6px; border-radius: 3px; background: #e2e8f0; outline: none; cursor: pointer; }
.metro-slider::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 22px; height: 22px; border-radius: 50%; background: #f59e0b; cursor: pointer; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.2); }
.adj-btn { padding: 0.375rem 0.75rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; background: transparent; color: #475569; font-family: monospace; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.15s; }
.adj-btn:hover { background: #f1f5f9; color: #1e293b; }
.tap-btn { padding: 0.375rem 1.25rem; border-radius: 0.5rem; border: 1px solid #fde68a; background: #fef3c7; color: #92400e; font-size: 0.875rem; font-weight: 700; cursor: pointer; }
.tap-btn:hover { background: #fde68a; }
#play-btn { width: 100%; padding: 1rem; border-radius: 0.75rem; border: none; font-size: 1.25rem; font-weight: 700; cursor: pointer; transition: all 0.15s; }
#play-btn.stopped { background: #22c55e; color: #fff; }
#play-btn.stopped:hover { background: #16a34a; }
#play-btn.playing { background: #ef4444; color: #fff; }
#play-btn.playing:hover { background: #dc2626; }
.song-badge { margin-top: 1rem; text-align: center; font-size: 0.875rem; color: #64748b; }

html.dark .beat-btn.lvl-1 { background: #334155; border-color: #475569; color: #94a3b8; }
html.dark .beat-btn.lvl-0 { border-color: #334155; color: #334155; }
html.dark .sig-btn         { border-color: #334155; color: #94a3b8; }
html.dark .sig-btn:hover   { border-color: #f59e0b; color: #fbbf24; }
html.dark .sig-btn.active  { background: #f59e0b; border-color: #d97706; color: #0f172a; }
html.dark .metro-card      { background: #1e293b; border-color: #334155; }
html.dark .metro-label     { color: #94a3b8; }
html.dark .metro-h1        { color: #f1f5f9; }
html.dark .bpm-num         { color: #f1f5f9; }
html.dark .metro-slider    { background: #334155; }
html.dark .metro-slider::-webkit-slider-thumb { border-color: #1e293b; }
html.dark .adj-btn         { border-color: #334155; color: #94a3b8; }
html.dark .adj-btn:hover   { background: #0f172a; color: #f1f5f9; }
html.dark .tap-btn         { background: #451a03; border-color: #78350f; color: #fde68a; }
html.dark .vdot.lvl-0      { background: #334155; }
</style>

<div class="max-w-2xl">

    <h1 class="metro-h1 mb-6">Metronóm</h1>

    <div class="metro-card p-6 space-y-6">

        {{-- BPM display --}}
        <div class="text-center">
            <div class="bpm-num" id="bpm-display">120</div>
            <div class="bpm-sub">BPM</div>
        </div>

        {{-- Slider --}}
        <div>
            <input type="range" id="bpm-slider" min="40" max="240" value="120" class="metro-slider">
            <div class="flex justify-between text-xs mt-1" style="color:#94a3b8">
                <span>40</span><span>120</span><span>240</span>
            </div>
        </div>

        {{-- Adjust + Tap --}}
        <div class="flex gap-2 justify-center flex-wrap">
            <button class="adj-btn" onclick="changeBpm(-10)">−10</button>
            <button class="adj-btn" onclick="changeBpm(-1)">−1</button>
            <button class="tap-btn" id="tap-btn" onclick="tapTempo()">TAP</button>
            <button class="adj-btn" onclick="changeBpm(+1)">+1</button>
            <button class="adj-btn" onclick="changeBpm(+10)">+10</button>
        </div>

        {{-- Time signature --}}
        <div>
            <div class="metro-label">Takt</div>
            <div class="flex gap-2 flex-wrap">
                @foreach(['2/4','3/4','4/4','5/4','6/8','7/8'] as $sig)
                <button class="sig-btn" data-sig="{{ $sig }}" onclick="setTimeSig('{{ $sig }}')">{{ $sig }}</button>
                @endforeach
            </div>
        </div>

        {{-- Beat accents --}}
        <div>
            <div class="metro-label">Doby <span style="font-weight:400;opacity:.7">(klikni pre zmenu sily)</span></div>
            <div class="flex gap-2 flex-wrap" id="beat-row"></div>
        </div>

        {{-- Visual indicator --}}
        <div class="flex gap-3 justify-center items-center" style="min-height:2rem" id="visual-row"></div>

        {{-- Play/stop --}}
        <button id="play-btn" class="stopped" onclick="togglePlay()">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 20 20" fill="currentColor" style="display:inline;vertical-align:-3px;margin-right:7px"><path d="M6.3 2.841A1.5 1.5 0 0 0 4 4.11V15.89a1.5 1.5 0 0 0 2.3 1.269l9.344-5.89a1.5 1.5 0 0 0 0-2.538L6.3 2.84z"/></svg>Spustiť
        </button>

        @if(request()->has('song'))
        <div class="song-badge">
            Pieseň: <strong>{{ e(request('song')) }}</strong>
        </div>
        @endif

    </div>

    <p class="text-xs mt-3" style="color:#94a3b8;text-align:center">Klávesnica: <kbd style="background:#e2e8f0;padding:1px 5px;border-radius:3px">Medzerník</kbd> = spustiť / zastaviť</p>

</div>

<script>
// ── State ─────────────────────────────────────────────────
let bpm      = parseInt(localStorage.getItem('metro_bpm') || '120');
let timeSig  = localStorage.getItem('metro_timesig') || '4/4';
let accents  = null;
let isPlaying = false;
let audioCtx  = null;
let nextNoteTime = 0;
let currentBeat  = 0;
let schedTimer   = null;
let visualBeat   = -1;
let tapTimes     = [];
let tapClearTimer = null;

// URL param (BPM z kliknutia na pieseň) má prednosť
const urlBpm = new URLSearchParams(window.location.search).get('bpm');
if (urlBpm) bpm = Math.min(240, Math.max(40, parseInt(urlBpm) || bpm));

// ── Helpers ───────────────────────────────────────────────
function getBeats() { return parseInt(timeSig.split('/')[0]); }

function defaultAccents(n) {
    return Array.from({length: n}, (_, i) => i === 0 ? 2 : 1);
}

function loadAccents() {
    try {
        const s = localStorage.getItem('metro_acc_' + timeSig);
        if (s) {
            const a = JSON.parse(s);
            if (a.length === getBeats()) return a;
        }
    } catch {}
    return defaultAccents(getBeats());
}

function saveAccents() {
    localStorage.setItem('metro_acc_' + timeSig, JSON.stringify(accents));
}

// ── Render ─────────────────────────────────────────────────
function renderBeats() {
    const row = document.getElementById('beat-row');
    row.innerHTML = '';
    accents.forEach((lvl, i) => {
        const btn = document.createElement('button');
        btn.className = 'beat-btn lvl-' + lvl;
        btn.textContent = i + 1;
        btn.title = ['Tichý','Slabý','Silný'][lvl];
        btn.onclick = () => { accents[i] = lvl === 2 ? 1 : lvl === 1 ? 0 : 2; saveAccents(); renderBeats(); renderVisual(); };
        row.appendChild(btn);
    });
}

function renderVisual() {
    const row = document.getElementById('visual-row');
    row.innerHTML = '';
    accents.forEach((lvl, i) => {
        const d = document.createElement('div');
        d.className = 'vdot lvl-' + lvl;
        d.id = 'vdot-' + i;
        row.appendChild(d);
    });
    visualBeat = -1;
}

function flashVisual(i) {
    if (visualBeat >= 0) document.getElementById('vdot-' + visualBeat)?.classList.remove('lit');
    visualBeat = i;
    document.getElementById('vdot-' + i)?.classList.add('lit');
}

// ── BPM ───────────────────────────────────────────────────
function setBpm(v) {
    bpm = Math.min(240, Math.max(40, v));
    document.getElementById('bpm-display').textContent = bpm;
    document.getElementById('bpm-slider').value = bpm;
    localStorage.setItem('metro_bpm', bpm);
}

function changeBpm(d) { setBpm(bpm + d); }

document.getElementById('bpm-slider').addEventListener('input', e => setBpm(+e.target.value));

// ── Tap tempo ─────────────────────────────────────────────
function tapTempo() {
    const now = performance.now();
    tapTimes.push(now);
    if (tapTimes.length > 8) tapTimes.shift();
    if (tapTimes.length >= 2) {
        let sum = 0;
        for (let i = 1; i < tapTimes.length; i++) sum += tapTimes[i] - tapTimes[i-1];
        setBpm(Math.round(60000 / (sum / (tapTimes.length - 1))));
    }
    const btn = document.getElementById('tap-btn');
    btn.style.opacity = '0.6';
    setTimeout(() => btn.style.opacity = '', 100);
    clearTimeout(tapClearTimer);
    tapClearTimer = setTimeout(() => tapTimes = [], 3000);
}

// ── Time sig ──────────────────────────────────────────────
function setTimeSig(sig) {
    const was = isPlaying;
    if (was) stopMetronome();
    timeSig = sig;
    localStorage.setItem('metro_timesig', sig);
    accents = loadAccents();
    document.querySelectorAll('.sig-btn').forEach(b => b.classList.toggle('active', b.dataset.sig === sig));
    renderBeats();
    renderVisual();
    if (was) startMetronome();
}

// ── Audio ─────────────────────────────────────────────────
function scheduleClick(beatIdx, time) {
    const lvl = accents[beatIdx];
    const delay = Math.max(0, (time - audioCtx.currentTime) * 1000);
    setTimeout(() => flashVisual(beatIdx), delay);
    if (lvl === 0) return;

    const osc  = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain);
    gain.connect(audioCtx.destination);

    osc.type = 'sine';
    osc.frequency.value = lvl === 2 ? 1600 : 900;
    const vol = lvl === 2 ? 0.75 : 0.35;
    const dur = 0.055;
    gain.gain.setValueAtTime(0, time);
    gain.gain.linearRampToValueAtTime(vol, time + 0.003);
    gain.gain.exponentialRampToValueAtTime(0.001, time + dur);
    osc.start(time);
    osc.stop(time + dur + 0.01);
}

function scheduler() {
    const beats   = getBeats();
    const spb     = 60.0 / bpm;
    while (nextNoteTime < audioCtx.currentTime + 0.12) {
        scheduleClick(currentBeat, nextNoteTime);
        nextNoteTime += spb;
        currentBeat = (currentBeat + 1) % beats;
    }
    schedTimer = setTimeout(scheduler, 25);
}

function startMetronome() {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (audioCtx.state === 'suspended') audioCtx.resume();
    isPlaying = true;
    currentBeat = 0;
    nextNoteTime = audioCtx.currentTime + 0.05;
    scheduler();
    const btn = document.getElementById('play-btn');
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 20 20" fill="currentColor" style="display:inline;vertical-align:-3px;margin-right:7px"><rect x="3" y="3" width="14" height="14" rx="2.5"/></svg>Zastaviť';
    btn.className = 'playing';
}

function stopMetronome() {
    isPlaying = false;
    clearTimeout(schedTimer); schedTimer = null;
    if (visualBeat >= 0) { document.getElementById('vdot-' + visualBeat)?.classList.remove('lit'); visualBeat = -1; }
    const btn = document.getElementById('play-btn');
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 20 20" fill="currentColor" style="display:inline;vertical-align:-3px;margin-right:7px"><path d="M6.3 2.841A1.5 1.5 0 0 0 4 4.11V15.89a1.5 1.5 0 0 0 2.3 1.269l9.344-5.89a1.5 1.5 0 0 0 0-2.538L6.3 2.84z"/></svg>Spustiť';
    btn.className = 'stopped';
}

function togglePlay() { isPlaying ? stopMetronome() : startMetronome(); }

document.addEventListener('keydown', e => {
    if (e.code === 'Space' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault(); togglePlay();
    }
});

// ── Init ──────────────────────────────────────────────────
(function () {
    setBpm(bpm);
    accents = loadAccents();
    document.querySelectorAll('.sig-btn').forEach(b => b.classList.toggle('active', b.dataset.sig === timeSig));
    renderBeats();
    renderVisual();
})();
</script>

@endsection
