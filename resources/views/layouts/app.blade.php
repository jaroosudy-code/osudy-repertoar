<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Repertoár') – Osudy</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PLAYLIST">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script>
    if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');
    // Stránka sa sama uloží do cache pri každom načítaní online
    if ('caches' in window) {
        window.addEventListener('load', function() {
            if (!navigator.onLine) return;
            caches.open('osudy-v7').then(function(cache) {
                var html = '<!DOCTYPE html>' + document.documentElement.outerHTML;
                cache.put(window.location.href, new Response(html, {
                    headers: { 'Content-Type': 'text/html; charset=utf-8' }
                }));
            });
        });
    }
    </script>
    <style>
        html, body { overflow-x: hidden; max-width: 100%; }
        @media (max-width: 639px) {
            .nav-settings-text { display: none; }
            #nav-inner { gap: 0.15rem; padding-left: 0.35rem; padding-right: 0.35rem; }
            #nav-inner .nav-logo { height: 2rem; }
            #nav-inner a { padding-left: 6px !important; padding-right: 6px !important; }
            #nav-inner form button { padding-left: 4px !important; padding-right: 4px !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen font-sans">

<nav class="bg-slate-900 text-white shadow-lg">
    <div id="nav-inner" class="max-w-7xl mx-auto px-4 flex items-center gap-8 h-14">
        <a href="{{ route('songs.index') }}" class="shrink-0">
            <img src="/logo.gif" alt="Osudy" class="nav-logo h-10 w-auto">
        </a>
        <div class="flex gap-1">
            <a href="{{ route('songs.index') }}"
               class="px-3 py-1.5 rounded text-sm font-medium transition-colors
                      {{ request()->routeIs('songs.*') ? 'bg-amber-500 text-slate-900' : 'text-slate-300 hover:bg-slate-700' }}">
                Piesne
            </a>
            <a href="{{ route('setlists.index') }}"
               class="px-3 py-1.5 rounded text-sm font-medium transition-colors
                      {{ request()->routeIs('setlists.*') ? 'bg-amber-500 text-slate-900' : 'text-slate-300 hover:bg-slate-700' }}">
                Playlisty
            </a>
        </div>
        @if(auth()->user()->isAdmin())
        {{-- Online zoznam v nave – len pre admina, len desktop --}}
        <div id="nav-online" class="hidden md:flex items-center gap-2 text-xs text-slate-400 mx-auto">
            <span id="nav-online-list"></span>
        </div>
        @endif

        <div class="ml-auto flex gap-1 items-center">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.users.index') }}"
               class="px-3 py-1.5 rounded text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.*') ? 'bg-amber-500 text-slate-900' : 'text-slate-300 hover:bg-slate-700' }}"
               title="Admin panel">⚙️</a>
            @endif
            <a href="{{ route('settings') }}"
               class="px-3 py-1.5 rounded text-sm font-medium transition-colors
                      {{ request()->routeIs('settings*') ? 'bg-amber-500 text-slate-900' : 'text-slate-300 hover:bg-slate-700' }}">
                ⚙ <span class="nav-settings-text">Nastavenia</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;display:inline">
                @csrf
                <button type="submit" title="Odhlásiť sa"
                        style="display:inline-flex;align-items:center;padding:6px 8px;border-radius:4px;border:none;background:none;color:#94a3b8;cursor:pointer;transition:background .15s,color .15s"
                        onmouseover="this.style.background='#334155';this.style.color='#fff'"
                        onmouseout="this.style.background='none';this.style.color='#94a3b8'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</nav>

<div id="offline-bar" style="display:none; background:#b45309; color:white; text-align:center; padding:6px; font-size:0.8rem;">
    ⚡ Offline režim — zobrazujú sa uložené stránky
</div>
<script>
    function updateOnlineStatus() {
        document.getElementById('offline-bar').style.display = navigator.onLine ? 'none' : 'block';
    }
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();
</script>

<main class="max-w-7xl mx-auto px-4 py-6">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg flex justify-between items-start">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-green-600 hover:text-green-800 font-bold">×</button>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg flex justify-between items-start">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-red-600 hover:text-red-800 font-bold">×</button>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

{{-- ═══════════════════════════════════════════════════════
     FLOATING CHAT WIDGET
═══════════════════════════════════════════════════════ --}}
<div id="chat-widget" style="position:fixed;bottom:20px;right:20px;z-index:1000;font-family:sans-serif;display:flex;flex-direction:column;align-items:flex-end;">

    {{-- Chat panel (hidden by default) --}}
    <div id="chat-popup" style="display:none;width:320px;height:460px;background:#fff;border-radius:12px;
         box-shadow:0 8px 32px rgba(0,0,0,0.18);border:1px solid #e2e8f0;
         flex-direction:column;overflow:hidden;margin-bottom:12px;">

        {{-- Panel: Conversation list --}}
        <div id="cp-list" style="display:flex;flex-direction:column;height:100%;">
            <div style="background:#0f172a;color:#fff;padding:12px 16px;display:flex;align-items:center;gap:8px;">
                <span style="font-size:16px">💬</span>
                <span style="font-weight:600;font-size:15px;flex:1">Chat</span>
                @if(auth()->user()->isAdmin())
                <span id="cp-online-summary" style="font-size:11px;color:#94a3b8"></span>
                @endif
<button id="cp-invis-btn" onclick="chatToggleInvisible()" title="{{ auth()->user()->is_invisible ? 'Si neviditeľný' : 'Neviditeľný režim' }}"
                        style="background:none;border:none;cursor:pointer;font-size:14px;padding:2px 5px;line-height:1;border-radius:4px;color:#94a3b8;transition:all .15s"
                        onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='none'">{{ auth()->user()->is_invisible ? '🙈' : '👁' }}</button>
                <button id="cp-sound-btn" onclick="chatToggleSound()" title="Zvuk upozornení"
                        style="background:none;border:none;cursor:pointer;font-size:14px;padding:2px 5px;line-height:1;border-radius:4px;color:#94a3b8;transition:all .15s"
                        onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='none'">🔔</button>
                <button onclick="chatClose()" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:18px;line-height:1;padding:0">×</button>
            </div>
            <div style="flex:1;overflow-y:auto;">
                {{-- Group --}}
                <button onclick="chatOpen(null,'Skupinový chat')"
                        id="cp-conv-group"
                        style="width:100%;display:flex;align-items:center;gap:12px;padding:12px 16px;border:none;background:none;cursor:pointer;border-bottom:1px solid #f1f5f9;text-align:left;transition:background .15s"
                        onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='none'">
                    <div style="width:38px;height:38px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🎵</div>
                    <div style="flex:1;min-width:0">
                        <p style="margin:0;font-size:14px;font-weight:600;color:#1e293b">Skupinový chat</p>
                        <p style="margin:2px 0 0;font-size:12px;color:#94a3b8">Všetci členovia</p>
                    </div>
                    <span id="cp-badge-group" style="display:none;background:#ef4444;color:#fff;border-radius:99px;padding:1px 7px;font-size:11px;font-weight:700"></span>
                </button>
                {{-- Users loaded via JS --}}
                <div id="cp-users-list"></div>
            </div>
        </div>

        {{-- Panel: Messages --}}
        <div id="cp-msgs" style="display:none;flex-direction:column;height:100%;">
            <div style="background:#0f172a;color:#fff;padding:10px 16px;display:flex;align-items:center;gap:10px;">
                <button onclick="chatBackToList()" style="background:none;border:none;color:#cbd5e1;cursor:pointer;font-size:18px;padding:0;line-height:1">←</button>
                <span id="cp-msgs-title" style="font-weight:600;font-size:15px;flex:1">Chat</span>
                <span id="cp-msgs-online" style="font-size:11px;color:#4ade80"></span>
                <button onclick="chatClose()" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:18px;line-height:1;padding:0">×</button>
            </div>
            <div id="cp-messages-box" style="flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:6px;scroll-behavior:smooth;">
                <p style="text-align:center;color:#cbd5e1;font-size:13px;margin:auto">Načítavam…</p>
            </div>
            <div style="padding:10px;border-top:1px solid #e2e8f0;">
                <form id="cp-form" onsubmit="chatSend(event)" style="display:flex;gap:8px;">
                    <input id="cp-input" type="text" maxlength="2000" autocomplete="off"
                           placeholder="Napíš správu…"
                           style="flex:1;border:1px solid #cbd5e1;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;">
                    <button type="submit"
                            style="background:#f59e0b;border:none;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer;color:#0f172a">
                        ➤
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Toggle button --}}
    <button id="chat-btn" onclick="chatToggle()"
            style="width:52px;height:52px;border-radius:50%;background:#f59e0b;border:none;cursor:pointer;
                   box-shadow:0 4px 12px rgba(0,0,0,0.2);font-size:22px;display:flex;align-items:center;
                   justify-content:center;position:relative;transition:transform .15s"
            onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
        💬
        <span id="chat-total-badge"
              style="display:none;position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;
                     border-radius:99px;min-width:18px;height:18px;font-size:11px;font-weight:700;
                     line-height:18px;text-align:center;padding:0 4px;border:2px solid #fff"></span>
    </button>
</div>

<script>
const _CSRF   = '{{ csrf_token() }}';
const _ME_ID  = {{ auth()->id() }};
const _IS_ADMIN = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};

let cpOpen       = false;
let cpActiveId   = 'LIST'; // 'LIST' | null (group) | number (user)
let cpLastId     = 0;
let cpPollTimer  = null;
let cpUsers      = [];
let cpUnread     = { group: 0, users: {} };

// ── Toggle ─────────────────────────────────────────────────────────────────────
function chatToggle() {
    cpOpen ? chatClose() : chatOpenWidget();
}

function chatOpenWidget() {
    cpOpen = true;
    document.getElementById('chat-popup').style.display = 'flex';
    if (!cpUsers.length) loadUsersAndUnread();
    else refreshUnread();
    if (_IS_ADMIN) refreshAdminOnline();
}

function chatClose() {
    cpOpen = false;
    document.getElementById('chat-popup').style.display = 'none';
    if (cpPollTimer) { clearInterval(cpPollTimer); cpPollTimer = null; }
}

// ── Load users & unread counts ─────────────────────────────────────────────────
async function loadUsersAndUnread() {
    try {
        const [uRes, urRes] = await Promise.all([
            fetch('/api/chat/users',         { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
            fetch('/api/chat/unread-detail', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
        ]);
        cpUsers  = await uRes.json();
        const ud = await urRes.json();
        cpUnread = { group: ud.group || 0, users: ud.users || {} };

        renderUsersList();
        updateBadges();
    } catch {}
}

async function refreshUnread() {
    try {
        const res = await fetch('/api/chat/unread-detail', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const ud  = await res.json();
        cpUnread  = { group: ud.group || 0, users: ud.users || {} };
        updateBadges();
    } catch {}
}

function renderUsersList() {
    const box = document.getElementById('cp-users-list');
    box.innerHTML = cpUsers.map(u => {
        const dot = u.online ? (u.is_invisible ? '#a78bfa' : '#22c55e') : '#cbd5e1';
        return `<button onclick="chatOpen(${u.id},'${u.name.replace(/'/g,"\\'")}')"
                    id="cp-conv-${u.id}"
                    style="width:100%;display:flex;align-items:center;gap:12px;padding:12px 16px;border:none;background:none;cursor:pointer;border-bottom:1px solid #f1f5f9;text-align:left"
                    onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='none'">
                <div style="position:relative;flex-shrink:0">
                    <div style="width:38px;height:38px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;color:#475569">
                        ${u.name[0].toUpperCase()}
                    </div>
                    <span style="position:absolute;bottom:1px;right:1px;width:10px;height:10px;border-radius:50%;background:${dot};border:2px solid #fff"></span>
                </div>
                <div style="flex:1;min-width:0">
                    <p style="margin:0;font-size:14px;font-weight:500;color:#1e293b">${esc(u.name)}</p>
                </div>
                <span id="cp-badge-${u.id}" style="display:none;background:#ef4444;color:#fff;border-radius:99px;padding:1px 7px;font-size:11px;font-weight:700"></span>
            </button>`;
    }).join('');
}

function updateBadges() {
    // Group badge
    const gb = document.getElementById('cp-badge-group');
    if (gb) {
        gb.textContent = cpUnread.group;
        gb.style.display = cpUnread.group ? 'block' : 'none';
    }
    // User badges
    cpUsers.forEach(u => {
        const cnt = cpUnread.users[u.id] || 0;
        const el  = document.getElementById('cp-badge-' + u.id);
        if (el) {
            el.textContent = cnt;
            el.style.display = cnt ? 'block' : 'none';
        }
    });
    // Total badge on toggle button
    const total = cpUnread.group + Object.values(cpUnread.users).reduce((a,b)=>a+(+b),0);
    const tb = document.getElementById('chat-total-badge');
    if (tb) {
        tb.textContent = total > 9 ? '9+' : total;
        tb.style.display = total ? 'block' : 'none';
    }
}

// ── Open a conversation ────────────────────────────────────────────────────────
function chatOpen(userId, name) {
    cpActiveId = userId;
    cpLastId   = 0;

    document.getElementById('cp-list').style.display  = 'none';
    document.getElementById('cp-msgs').style.display  = 'flex';

    document.getElementById('cp-msgs-title').textContent = name;
    document.getElementById('cp-msgs-online').textContent = '';

    const box = document.getElementById('cp-messages-box');
    box.innerHTML = '<p style="text-align:center;color:#cbd5e1;font-size:13px;margin:auto">Načítavam…</p>';

    // Clear badge for this conv
    const key = userId === null ? 'group' : userId;
    const badgeEl = document.getElementById('cp-badge-' + key);
    if (badgeEl) { badgeEl.style.display = 'none'; }

    if (cpPollTimer) clearInterval(cpPollTimer);
    fetchChatMessages();
    cpPollTimer = setInterval(fetchChatMessages, 4000);

    fetch('/chat/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': _CSRF } });
    setTimeout(() => document.getElementById('cp-input').focus(), 50);
}

function chatBackToList() {
    if (cpPollTimer) { clearInterval(cpPollTimer); cpPollTimer = null; }
    document.getElementById('cp-msgs').style.display = 'none';
    document.getElementById('cp-list').style.display = 'flex';
    cpActiveId = 'LIST';
    refreshUnread();
}

// ── Messages ───────────────────────────────────────────────────────────────────
async function fetchChatMessages() {
    if (cpActiveId === 'LIST') return;
    const url = cpActiveId
        ? '/api/chat/messages?since_id=' + cpLastId + '&user_id=' + cpActiveId
        : '/api/chat/messages?since_id=' + cpLastId;
    try {
        const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const msgs = await res.json();
        if (!Array.isArray(msgs) || !msgs.length) return;
        appendChatMessages(msgs);
        cpLastId = msgs[msgs.length - 1].id;
    } catch {}
}

function appendChatMessages(msgs) {
    const box = document.getElementById('cp-messages-box');
    const atBottom = box.scrollHeight - box.clientHeight - box.scrollTop < 80;

    const ph = box.querySelector('p');
    if (ph && (ph.style.color === 'rgb(203, 213, 225)' || ph.style.margin === 'auto')) ph.remove();

    msgs.forEach(m => {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;' + (m.is_mine ? 'justify-content:flex-end' : 'justify-content:flex-start');

        if (m.is_mine) {
            wrap.innerHTML = `<div style="max-width:78%">
                <div style="background:#f59e0b;color:#0f172a;border-radius:14px 14px 3px 14px;padding:7px 11px;font-size:13px;line-height:1.4">${esc(m.body)}</div>
                <p style="text-align:right;font-size:10px;color:#94a3b8;margin:2px 3px 0">${m.created_at}</p>
            </div>`;
        } else {
            wrap.innerHTML = `<div style="max-width:78%">
                ${cpActiveId === null ? `<p style="font-size:10px;color:#64748b;margin:0 0 2px 3px">${esc(m.sender)}</p>` : ''}
                <div style="background:#f1f5f9;color:#1e293b;border-radius:14px 14px 14px 3px;padding:7px 11px;font-size:13px;line-height:1.4">${esc(m.body)}</div>
                <p style="font-size:10px;color:#94a3b8;margin:2px 0 0 3px">${m.created_at}</p>
            </div>`;
        }
        box.appendChild(wrap);
    });

    if (atBottom) box.scrollTop = box.scrollHeight;
}

async function chatSend(e) {
    e.preventDefault();
    const inp  = document.getElementById('cp-input');
    const body = inp.value.trim();
    if (!body) return;
    inp.value = '';
    const payload = { body };
    if (cpActiveId) payload.recipient_id = cpActiveId;
    try {
        const res = await fetch('/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _CSRF },
            body: JSON.stringify(payload),
        });
        const msg = await res.json();
        if (msg.id) { appendChatMessages([msg]); cpLastId = msg.id; }
    } catch {}
    inp.focus();
}

// ── Sound & invisible state (musí byť pred funkciami ktoré ich používajú) ────────
let chatSoundEnabled = localStorage.getItem('chatSound') !== '0';
let lastKnownTotal   = 0;

async function chatToggleInvisible() {
    try {
        const res  = await fetch('/settings/invisible', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': _CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        const data = await res.json();
        const btn  = document.getElementById('cp-invis-btn');
        if (btn) {
            btn.textContent = data.is_invisible ? '🙈' : '👁';
            btn.title = data.is_invisible ? 'Si neviditeľný – klikni pre zobrazenie' : 'Neviditeľný režim';
            btn.style.color = data.is_invisible ? '#a78bfa' : '#94a3b8';
        }
    } catch {}
}


function chatToggleSound() {
    chatSoundEnabled = !chatSoundEnabled;
    localStorage.setItem('chatSound', chatSoundEnabled ? '1' : '0');
    updateSoundBtn();
    if (chatSoundEnabled) playNotifSound();
}
function updateSoundBtn() {
    const btn = document.getElementById('cp-sound-btn');
    if (!btn) return;
    if (chatSoundEnabled) {
        btn.textContent = '🔔';
        btn.style.color = '#94a3b8';
        btn.style.textDecoration = 'none';
        btn.title = 'Zvuk zapnutý – klikni pre vypnutie';
    } else {
        btn.textContent = '🔕';
        btn.style.color = '#ef4444';
        btn.style.textDecoration = 'none';
        btn.title = 'Zvuk vypnutý – klikni pre zapnutie';
    }
}
updateSoundBtn();

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
}

// ── Admin: who's online ────────────────────────────────────────────────────────
async function refreshAdminOnline() {
    try {
        const res  = await fetch('/api/chat/online', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const list = await res.json();

        // Popup header
        const popupEl = document.getElementById('cp-online-summary');
        if (popupEl) popupEl.textContent = list.length ? list.map(u => (u.is_invisible ? '👁 ' : '🟢 ') + u.name).join('  ') : '';

        // Nav bar
        const navEl = document.getElementById('nav-online-list');
        if (navEl) {
            navEl.innerHTML = list.length
                ? list.map(u => `<span style="display:inline-flex;align-items:center;gap:4px">
                    <span style="width:7px;height:7px;border-radius:50%;background:${u.is_invisible ? '#a78bfa' : '#22c55e'}"></span>
                    <span style="color:${u.is_invisible ? '#a78bfa' : '#94a3b8'}">${esc(u.name)}${u.is_invisible ? ' 👁' : ''}</span>
                  </span>`).join('<span style="color:#334155;margin:0 6px">·</span>')
                : '';
        }
    } catch {}
}

// ── Polling for total badge (when widget is closed) ────────────────────────────
async function pollTotalUnread() {
    if (cpOpen) return;
    try {
        const res  = await fetch('/api/chat/unread', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        const count = data.count || 0;
        const tb   = document.getElementById('chat-total-badge');
        if (tb) {
            tb.textContent = count > 9 ? '9+' : count;
            tb.style.display = count ? 'block' : 'none';
        }
        if (count > lastKnownTotal) playNotifSound();
        lastKnownTotal = count;
    } catch {}
}

// ── Notification sound ────────────────────────────────────────────────────────
function playNotifSound() {
    if (!chatSoundEnabled) return;
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        osc.frequency.setValueAtTime(1100, ctx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.4);
    } catch {}
}

pollTotalUnread();
setInterval(pollTotalUnread, 10000);
if (_IS_ADMIN) { refreshAdminOnline(); setInterval(refreshAdminOnline, 20000); }

// Enter to send
document.getElementById('cp-input').addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('cp-form').dispatchEvent(new Event('submit'));
    }
});
</script>

</body>
</html>
