@extends('layouts.app')
@section('title', 'Chat')

@section('content')
@php $me = auth()->user(); @endphp

<div class="flex gap-4" style="height:calc(100vh - 130px); min-height:440px;">

    {{-- LEFT: Conversations --}}
    <div id="conv-panel"
         class="w-64 shrink-0 flex flex-col bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
         style="display:flex !important;">

        <div class="px-4 py-3 border-b border-slate-200">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Konverzácie</p>
        </div>

        <div class="flex-1 overflow-y-auto">
            {{-- Group chat --}}
            <button onclick="openConversation(null, 'Skupinový chat', '🎵')"
                    id="conv-btn-group"
                    class="conv-item w-full flex items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-amber-50 border-b border-slate-100">
                <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center text-lg shrink-0">🎵</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-700">Skupinový chat</p>
                    <p class="text-xs text-slate-400">Všetci členovia</p>
                </div>
                <span id="badge-group" style="display:none" class="text-xs bg-red-500 text-white rounded-full px-1.5 font-bold">!</span>
            </button>

            {{-- Individual users --}}
            @foreach($users as $user)
            <button onclick="openConversation({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ strtoupper(mb_substr($user->name,0,1)) }}')"
                    id="conv-btn-{{ $user->id }}"
                    class="conv-item w-full flex items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-amber-50 border-b border-slate-100">
                <div class="relative shrink-0">
                    <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-sm font-bold text-slate-600">
                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>
                    <span id="dot-{{ $user->id }}"
                          class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-white bg-slate-300"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700">{{ $user->name }}</p>
                    <p class="text-xs text-slate-400 truncate" id="preview-{{ $user->id }}">—</p>
                </div>
                <span id="badge-{{ $user->id }}" style="display:none" class="text-xs bg-red-500 text-white rounded-full px-1.5 font-bold">!</span>
            </button>
            @endforeach
        </div>
    </div>

    {{-- RIGHT: Chat window --}}
    <div id="chat-panel" class="flex flex-col flex-1 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" style="display:none;">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 border-b border-slate-200">
            <div id="chat-avatar" class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-base font-bold shrink-0">🎵</div>
            <p id="chat-title" class="font-semibold text-slate-700 flex-1 text-base">Skupinový chat</p>
            <span id="chat-online" class="text-xs text-green-600"></span>
        </div>

        {{-- Messages --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3" style="scroll-behavior:smooth;">
            <p class="text-center text-slate-300 text-sm py-10">Načítavam správy…</p>
        </div>

        {{-- Input --}}
        <div class="border-t border-slate-200 px-4 py-3">
            <form id="msg-form" class="flex gap-2" onsubmit="sendMessage(event)">
                <input type="text" id="msg-input"
                       class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                       placeholder="Napíš správu…" autocomplete="off" maxlength="2000">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-5 py-2 rounded-lg text-sm transition-colors shrink-0">
                    Odoslať
                </button>
            </form>
        </div>
    </div>

</div>

<script>
const ME_ID = {{ auth()->id() }};
const CSRF  = '{{ csrf_token() }}';

let activeId   = null;   // null = group, number = user id
let lastMsgId  = 0;
let pollTimer  = null;

// ── Open conversation ──────────────────────────────────────────────────────────

function openConversation(userId, name, initial) {
    activeId  = userId;
    lastMsgId = 0;

    // Highlight selected
    document.querySelectorAll('.conv-item').forEach(b => {
        b.style.background = '';
        b.style.borderLeft = '';
    });
    const key = userId !== null ? userId : 'group';
    const btn = document.getElementById('conv-btn-' + key);
    if (btn) { btn.style.background = '#fffbeb'; btn.style.borderLeft = '3px solid #f59e0b'; }

    // Header
    const isGroup = (userId === null);
    const av = document.getElementById('chat-avatar');
    av.textContent   = isGroup ? '🎵' : initial;
    av.style.background = isGroup ? '#fef3c7' : '#e2e8f0';
    document.getElementById('chat-title').textContent = name;
    document.getElementById('chat-online').textContent = '';

    // Show panel
    document.getElementById('chat-panel').style.display = 'flex';

    // Clear & load
    document.getElementById('chat-messages').innerHTML = '<p class="text-center text-slate-300 text-sm py-10">Načítavam…</p>';
    hideBadge(key);

    if (pollTimer) clearInterval(pollTimer);
    fetchMessages();
    pollTimer = setInterval(fetchMessages, 4000);

    fetch('/chat/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    setTimeout(() => document.getElementById('msg-input').focus(), 100);
}

// ── Fetch & render messages ────────────────────────────────────────────────────

async function fetchMessages() {
    const url = activeId
        ? '/api/chat/messages?since_id=' + lastMsgId + '&user_id=' + activeId
        : '/api/chat/messages?since_id=' + lastMsgId;
    try {
        const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const msgs = await res.json();
        if (!Array.isArray(msgs) || !msgs.length) return;
        renderMessages(msgs);
        lastMsgId = msgs[msgs.length - 1].id;
    } catch {}
}

function renderMessages(msgs) {
    const box = document.getElementById('chat-messages');
    const atBottom = box.scrollHeight - box.clientHeight - box.scrollTop < 80;

    const ph = box.querySelector('p');
    if (ph && ph.classList.contains('text-slate-300')) ph.remove();

    msgs.forEach(m => {
        const wrap = document.createElement('div');
        wrap.className = m.is_mine ? 'flex justify-end' : 'flex justify-start';
        if (m.is_mine) {
            wrap.innerHTML =
                '<div style="max-width:72%">'
                + '<div style="background:#f59e0b;color:#0f172a;border-radius:16px 16px 4px 16px;padding:8px 12px;font-size:14px">' + esc(m.body) + '</div>'
                + '<p style="text-align:right;font-size:11px;color:#94a3b8;margin:2px 4px 0">' + m.created_at + '</p>'
                + '</div>';
        } else {
            wrap.innerHTML =
                '<div style="max-width:72%">'
                + (activeId === null ? '<p style="font-size:11px;color:#64748b;margin:0 0 2px 4px">' + esc(m.sender) + '</p>' : '')
                + '<div style="background:#f1f5f9;color:#1e293b;border-radius:16px 16px 16px 4px;padding:8px 12px;font-size:14px">' + esc(m.body) + '</div>'
                + '<p style="font-size:11px;color:#94a3b8;margin:2px 0 0 4px">' + m.created_at + '</p>'
                + '</div>';
        }
        box.appendChild(wrap);
    });

    if (atBottom) box.scrollTop = box.scrollHeight;
}

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/\n/g,'<br>');
}

// ── Send ───────────────────────────────────────────────────────────────────────

async function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('msg-input');
    const body  = input.value.trim();
    if (!body) return;
    input.value = '';
    const payload = { body };
    if (activeId) payload.recipient_id = activeId;
    try {
        const res = await fetch('/chat/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload),
        });
        const msg = await res.json();
        if (msg.id) { renderMessages([msg]); lastMsgId = msg.id; }
    } catch {}
    input.focus();
}

// ── Online dots ────────────────────────────────────────────────────────────────

async function refreshOnline() {
    try {
        const res  = await fetch('/api/chat/online', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const list = await res.json();
        @foreach($users as $user)
        (function() {
            const u = list.find(x => x.id === {{ $user->id }});
            const dot = document.getElementById('dot-{{ $user->id }}');
            if (dot) dot.style.background = u ? (u.is_invisible ? '#a78bfa' : '#22c55e') : '#cbd5e1';
            if (u && activeId === {{ $user->id }}) {
                document.getElementById('chat-online').textContent = u.is_invisible ? '👁 neviditeľný' : '● online';
            }
        })();
        @endforeach
    } catch {}
}

function hideBadge(key) {
    const el = document.getElementById('badge-' + key);
    if (el) el.style.display = 'none';
}

// ── Init ───────────────────────────────────────────────────────────────────────

refreshOnline();
setInterval(refreshOnline, 15000);

// Auto-open group chat
openConversation(null, 'Skupinový chat', '🎵');

// Enter to send
document.getElementById('msg-input').addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); document.getElementById('msg-form').dispatchEvent(new Event('submit')); }
});
</script>
@endsection
