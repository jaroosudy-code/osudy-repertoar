{{-- ══════════════════════════════════════════════════════════
     METRONÓM MODAL  –  zdieľaný partial
     Otvorenie: openMModal(bpm, songName)
══════════════════════════════════════════════════════════ --}}

<style>
#mm-overlay {
    display: none; position: fixed; inset: 0; z-index: 9000;
    background: rgba(0,0,0,0.55); backdrop-filter: blur(3px);
    align-items: center; justify-content: center; padding: 1rem;
}
#mm-overlay.open { display: flex; }
#mm-card {
    background: #fff; border-radius: 1rem;
    padding: 1.25rem 1.5rem 1.5rem;
    width: 100%; max-width: 400px; max-height: 90vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4); position: relative;
}
#mm-pill {
    display: none; position: fixed; bottom: 20px; right: 86px;
    z-index: 990; background: #f59e0b; border: none;
    border-radius: 999px; padding: 10px 16px; gap: 7px;
    color: #1e293b; font-size: 0.8rem; font-weight: 700;
    cursor: pointer; align-items: center;
    box-shadow: 0 4px 14px rgba(245,158,11,0.5);
    animation: mm-pulse 0.7s ease-in-out infinite alternate;
}
#mm-pill.visible { display: flex; }
@keyframes mm-pulse {
    from { box-shadow: 0 4px 14px rgba(245,158,11,0.45); }
    to   { box-shadow: 0 4px 24px rgba(245,158,11,0.9);  }
}
.mm-beat-btn {
    width: 2.75rem; height: 2.75rem; border-radius: .5rem; border: 2px solid;
    font-size: .85rem; font-weight: 700; cursor: pointer; transition: all .1s;
    display: flex; align-items: center; justify-content: center;
}
.mm-beat-btn.lvl-2 { background: #f59e0b; border-color: #d97706; color: #1e293b; }
.mm-beat-btn.lvl-1 { background: #e2e8f0; border-color: #94a3b8; color: #475569; }
.mm-beat-btn.lvl-0 { background: transparent; border-color: #e2e8f0; color: #cbd5e1; }
.mm-vdot { width: 1.1rem; height: 1.1rem; border-radius: 50%; transition: transform .05s, opacity .05s; flex-shrink: 0; }
.mm-vdot.lvl-2 { background: #d97706; opacity: .35; }
.mm-vdot.lvl-1 { background: #94a3b8; opacity: .25; }
.mm-vdot.lvl-0 { background: #e2e8f0; opacity: .2;  }
.mm-vdot.lit   { opacity: 1 !important; transform: scale(1.55); }
.mm-sig-btn {
    padding: .3rem .75rem; border-radius: .5rem; border: 2px solid #e2e8f0;
    background: transparent; font-size: .8rem; font-family: monospace;
    font-weight: 600; color: #475569; cursor: pointer; transition: all .15s;
}
.mm-sig-btn:hover  { border-color: #f59e0b; color: #92400e; }
.mm-sig-btn.active { background: #f59e0b; border-color: #d97706; color: #1e293b; }
.mm-adj { padding: .3rem .7rem; border-radius: .5rem; border: 1px solid #e2e8f0; background: transparent; color: #475569; font-family: monospace; font-size: .8rem; font-weight: 600; cursor: pointer; transition: background .1s; }
.mm-adj:hover { background: #f1f5f9; }
.mm-tap { padding: .3rem 1.1rem; border-radius: .5rem; border: 1px solid #fde68a; background: #fef3c7; color: #92400e; font-size: .8rem; font-weight: 700; cursor: pointer; }
#mm-playbtn { width: 100%; padding: .875rem; border-radius: .75rem; border: none; font-size: 1rem; font-weight: 700; cursor: pointer; }
#mm-playbtn.stopped { background: #22c55e; color: #fff; }
#mm-playbtn.stopped:hover { background: #16a34a; }
#mm-playbtn.playing  { background: #ef4444; color: #fff; }
#mm-playbtn.playing:hover  { background: #dc2626; }
.mm-lbl { font-size: .72rem; font-weight: 600; color: #64748b; margin-bottom: .4rem; }

html.dark #mm-card          { background: #1e293b; }
html.dark #mm-bpm-num       { color: #f1f5f9 !important; }
html.dark #mm-title         { color: #f1f5f9 !important; }
html.dark #mm-close         { background: #0f172a !important; color: #94a3b8 !important; }
html.dark .mm-beat-btn.lvl-1 { background: #334155; border-color: #475569; color: #94a3b8; }
html.dark .mm-beat-btn.lvl-0 { border-color: #334155; color: #334155; }
html.dark .mm-sig-btn        { border-color: #334155; color: #94a3b8; }
html.dark .mm-sig-btn:hover  { border-color: #f59e0b; color: #fbbf24; }
html.dark .mm-sig-btn.active { background: #f59e0b; border-color: #d97706; color: #0f172a; }
html.dark .mm-adj            { border-color: #334155; color: #94a3b8; }
html.dark .mm-adj:hover      { background: #0f172a; color: #f1f5f9; }
html.dark .mm-tap            { background: #451a03; border-color: #78350f; color: #fde68a; }
html.dark .mm-lbl            { color: #475569; }
html.dark .mm-vdot.lvl-0    { background: #334155; }
</style>

{{-- Overlay --}}
<div id="mm-overlay">
    <div id="mm-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1rem">
            <div>
                <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;font-weight:600">Metronóm</div>
                <div id="mm-title" style="font-size:1rem;font-weight:700;color:#1e293b;margin-top:2px;line-height:1.2;max-width:280px"></div>
            </div>
            <button id="mm-close"
                    style="background:#f1f5f9;border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;color:#64748b;flex-shrink:0;margin-left:12px">×</button>
        </div>

        <div style="text-align:center;margin-bottom:.875rem">
            <div id="mm-bpm-num" style="font-size:3.75rem;font-weight:800;font-family:monospace;line-height:1;color:#1e293b"></div>
            <div style="font-size:.7rem;color:#94a3b8;font-weight:600;letter-spacing:.1em">BPM</div>
        </div>

        <div style="margin-bottom:.875rem">
            <input type="range" id="mm-slider" min="40" max="240" value="120" style="width:100%;accent-color:#f59e0b">
        </div>

        <div style="display:flex;gap:6px;justify-content:center;margin-bottom:1rem;flex-wrap:wrap">
            <button class="mm-adj" data-d="-10">−10</button>
            <button class="mm-adj" data-d="-1">−1</button>
            <button class="mm-tap" id="mm-tap-btn">TAP</button>
            <button class="mm-adj" data-d="+1">+1</button>
            <button class="mm-adj" data-d="+10">+10</button>
        </div>

        <div style="margin-bottom:1rem">
            <div class="mm-lbl">Takt</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap" id="mm-sigs">
                @foreach(['2/4','3/4','4/4','5/4','6/8','7/8'] as $sig)
                <button class="mm-sig-btn" data-sig="{{ $sig }}">{{ $sig }}</button>
                @endforeach
            </div>
        </div>

        <div style="margin-bottom:1rem">
            <div class="mm-lbl">Doby <span style="font-weight:400;opacity:.7">(klikni pre zmenu)</span></div>
            <div style="display:flex;gap:6px;flex-wrap:wrap" id="mm-beats"></div>
        </div>

        <div style="display:flex;gap:10px;justify-content:center;align-items:center;min-height:1.5rem;margin-bottom:1rem" id="mm-visual"></div>

        <button id="mm-playbtn" class="stopped">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="display:inline;vertical-align:-3px;margin-right:6px"><path d="M6.3 2.841A1.5 1.5 0 0 0 4 4.11V15.89a1.5 1.5 0 0 0 2.3 1.269l9.344-5.89a1.5 1.5 0 0 0 0-2.538L6.3 2.84z"/></svg>Spustiť
        </button>
    </div>
</div>

{{-- Plovúca kapsula – viditeľná keď beží + modal je skrytý --}}
<button id="mm-pill">
    <span>♩</span>
    <span id="mm-pill-bpm">120</span>
    <span style="opacity:.7">BPM</span>
    <span id="mm-pill-name" style="font-weight:400;opacity:.6;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
</button>

<script>
(function () {
// ── State ──────────────────────────────────────────────────────────
let mmBpm     = parseInt(localStorage.getItem('metro_bpm') || '120');
let mmTimeSig = localStorage.getItem('metro_timesig') || '4/4';
let mmAccents = null;
let mmRunning = false;
let mmAudioCtx = null;
let mmNextNote = 0, mmCurBeat = 0, mmSched = null, mmVisBeat = -1;
let mmTaps = [], mmTapTimer = null;
let mmName = '';

function mmBeats() { return parseInt(mmTimeSig.split('/')[0]); }

function mmDefaultAcc(n) { return Array.from({length: n}, (_, i) => i === 0 ? 2 : 1); }

function mmLoadAcc() {
    try {
        const s = localStorage.getItem('metro_acc_' + mmTimeSig);
        if (s) { const a = JSON.parse(s); if (a.length === mmBeats()) return a; }
    } catch {}
    return mmDefaultAcc(mmBeats());
}
function mmSaveAcc() { localStorage.setItem('metro_acc_' + mmTimeSig, JSON.stringify(mmAccents)); }

// ── DOM refs ───────────────────────────────────────────────────────
const mmOverlay   = document.getElementById('mm-overlay');
const mmPill      = document.getElementById('mm-pill');
const mmCloseBtn  = document.getElementById('mm-close');
const mmPlayBtn   = document.getElementById('mm-playbtn');
const mmSlider    = document.getElementById('mm-slider');
const mmTapBtn    = document.getElementById('mm-tap-btn');
const mmBpmNum    = document.getElementById('mm-bpm-num');
const mmPillBpm   = document.getElementById('mm-pill-bpm');
const mmPillName  = document.getElementById('mm-pill-name');
const mmTitleEl   = document.getElementById('mm-title');
const mmBeatsEl   = document.getElementById('mm-beats');
const mmVisEl     = document.getElementById('mm-visual');

// ── Open / Close ───────────────────────────────────────────────────
window.openMModal = function (bpm, name) {
    if (mmRunning) mmStop();
    mmBpm  = Math.min(240, Math.max(40, +bpm || mmBpm));
    mmName = name || '';
    mmAccents = mmLoadAcc();
    mmRefresh();
    mmOverlay.classList.add('open');
    mmPill.classList.remove('visible');
};

function mmHideModal() {
    mmOverlay.classList.remove('open');
    if (mmRunning) {
        mmPillBpm.textContent  = mmBpm;
        mmPillName.textContent = mmName ? '· ' + mmName : '';
        mmPill.classList.add('visible');
    }
}

// ── Pill → reopen ──────────────────────────────────────────────────
mmPill.addEventListener('click', () => {
    mmPill.classList.remove('visible');
    mmRefresh();
    mmOverlay.classList.add('open');
});

// ── Close btn + overlay click ──────────────────────────────────────
mmCloseBtn.addEventListener('click', mmHideModal);
mmOverlay.addEventListener('click', e => { if (e.target === mmOverlay) mmHideModal(); });

// ── Render ─────────────────────────────────────────────────────────
function mmRefresh() {
    mmTitleEl.textContent = mmName;
    mmBpmNum.textContent  = mmBpm;
    mmSlider.value        = mmBpm;
    document.querySelectorAll('.mm-sig-btn').forEach(b => b.classList.toggle('active', b.dataset.sig === mmTimeSig));
    mmRenderBeats();
    mmRenderVisual();
    mmUpdateBtn();
}

function mmRenderBeats() {
    mmBeatsEl.innerHTML = '';
    mmAccents.forEach((lvl, i) => {
        const btn = document.createElement('button');
        btn.className = 'mm-beat-btn lvl-' + lvl;
        btn.textContent = i + 1;
        btn.title = ['Tichý','Slabý','Silný'][lvl];
        btn.addEventListener('click', () => {
            mmAccents[i] = lvl === 2 ? 1 : lvl === 1 ? 0 : 2;
            mmSaveAcc();
            mmRenderBeats();
            mmRenderVisual();
        });
        mmBeatsEl.appendChild(btn);
    });
}

function mmRenderVisual() {
    mmVisEl.innerHTML = '';
    mmAccents.forEach((lvl, i) => {
        const d = document.createElement('div');
        d.className = 'mm-vdot lvl-' + lvl;
        d.id = 'mm-vd-' + i;
        mmVisEl.appendChild(d);
    });
    mmVisBeat = -1;
}

function mmFlash(i) {
    if (mmVisBeat >= 0) document.getElementById('mm-vd-' + mmVisBeat)?.classList.remove('lit');
    mmVisBeat = i;
    document.getElementById('mm-vd-' + i)?.classList.add('lit');
}

function mmUpdateBtn() {
    const svgPlay = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="display:inline;vertical-align:-3px;margin-right:6px"><path d="M6.3 2.841A1.5 1.5 0 0 0 4 4.11V15.89a1.5 1.5 0 0 0 2.3 1.269l9.344-5.89a1.5 1.5 0 0 0 0-2.538L6.3 2.84z"/></svg>';
    const svgStop = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="display:inline;vertical-align:-3px;margin-right:6px"><rect x="3" y="3" width="14" height="14" rx="2.5"/></svg>';
    mmPlayBtn.innerHTML = mmRunning ? svgStop + 'Zastaviť' : svgPlay + 'Spustiť';
    mmPlayBtn.className = mmRunning ? 'playing' : 'stopped';
}

// ── BPM ───────────────────────────────────────────────────────────
function mmSetBpm(v) {
    mmBpm = Math.min(240, Math.max(40, v));
    mmBpmNum.textContent  = mmBpm;
    mmSlider.value        = mmBpm;
    mmPillBpm.textContent = mmBpm;
    localStorage.setItem('metro_bpm', mmBpm);
}

mmSlider.addEventListener('input', e => mmSetBpm(+e.target.value));

document.querySelectorAll('.mm-adj').forEach(btn =>
    btn.addEventListener('click', () => mmSetBpm(mmBpm + (+btn.dataset.d)))
);

mmTapBtn.addEventListener('click', () => {
    const now = performance.now();
    mmTaps.push(now);
    if (mmTaps.length > 8) mmTaps.shift();
    if (mmTaps.length >= 2) {
        let s = 0;
        for (let i = 1; i < mmTaps.length; i++) s += mmTaps[i] - mmTaps[i-1];
        mmSetBpm(Math.round(60000 / (s / (mmTaps.length - 1))));
    }
    mmTapBtn.style.opacity = '0.5';
    setTimeout(() => mmTapBtn.style.opacity = '', 100);
    clearTimeout(mmTapTimer);
    mmTapTimer = setTimeout(() => mmTaps = [], 3000);
});

// ── Time sig ──────────────────────────────────────────────────────
document.querySelectorAll('.mm-sig-btn').forEach(btn =>
    btn.addEventListener('click', () => {
        const was = mmRunning;
        if (was) mmStop();
        mmTimeSig = btn.dataset.sig;
        localStorage.setItem('metro_timesig', mmTimeSig);
        mmAccents = mmLoadAcc();
        document.querySelectorAll('.mm-sig-btn').forEach(b => b.classList.toggle('active', b.dataset.sig === mmTimeSig));
        mmRenderBeats();
        mmRenderVisual();
        if (was) mmStart();
    })
);

// ── Audio ──────────────────────────────────────────────────────────
function mmClick(beatIdx, time) {
    const lvl = mmAccents[beatIdx];
    const delay = Math.max(0, (time - mmAudioCtx.currentTime) * 1000);
    setTimeout(() => mmFlash(beatIdx), delay);
    if (lvl === 0) return;
    const osc = mmAudioCtx.createOscillator();
    const g   = mmAudioCtx.createGain();
    osc.connect(g); g.connect(mmAudioCtx.destination);
    osc.type = 'sine';
    osc.frequency.value = lvl === 2 ? 1600 : 900;
    const vol = lvl === 2 ? 0.75 : 0.35;
    g.gain.setValueAtTime(0, time);
    g.gain.linearRampToValueAtTime(vol, time + 0.003);
    g.gain.exponentialRampToValueAtTime(0.001, time + 0.055);
    osc.start(time); osc.stop(time + 0.07);
}

function mmScheduler() {
    const beats = mmBeats(), spb = 60.0 / mmBpm;
    while (mmNextNote < mmAudioCtx.currentTime + 0.12) {
        mmClick(mmCurBeat, mmNextNote);
        mmNextNote += spb;
        mmCurBeat = (mmCurBeat + 1) % beats;
    }
    mmSched = setTimeout(mmScheduler, 25);
}

function mmStart() {
    if (!mmAudioCtx) mmAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (mmAudioCtx.state === 'suspended') mmAudioCtx.resume();
    mmRunning = true; mmCurBeat = 0;
    mmNextNote = mmAudioCtx.currentTime + 0.05;
    mmScheduler();
    mmUpdateBtn();
}

function mmStop() {
    mmRunning = false;
    clearTimeout(mmSched); mmSched = null;
    if (mmVisBeat >= 0) { document.getElementById('mm-vd-' + mmVisBeat)?.classList.remove('lit'); mmVisBeat = -1; }
    mmPill.classList.remove('visible');
    mmUpdateBtn();
}

mmPlayBtn.addEventListener('click', () => mmRunning ? mmStop() : mmStart());

// ── Init ───────────────────────────────────────────────────────────
mmAccents = mmLoadAcc();
document.querySelectorAll('.mm-sig-btn').forEach(b => b.classList.toggle('active', b.dataset.sig === mmTimeSig));

})();
</script>
