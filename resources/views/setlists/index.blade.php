@extends('layouts.app')
@section('title', 'Playlisty')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Playlisty</h1>
    @if(auth()->user()->hasPermission('setlists.create'))
    <a href="{{ route('setlists.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
        + Nový playlist
    </a>
    @endif
</div>

@if($setlists->isEmpty())
    <div class="text-center py-16 text-slate-400">
        <div class="text-5xl mb-3">📋</div>
        <p class="text-lg">Zatiaľ žiadne playlisty.
            @if(auth()->user()->hasPermission('setlists.create'))
                <a href="{{ route('setlists.create') }}" class="text-amber-500 hover:underline">Vytvor prvý!</a>
            @endif
        </p>
    </div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($setlists as $setlist)
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h2 class="font-bold text-slate-800 dark:text-slate-100 text-lg leading-tight">{{ $setlist->name }}</h2>
                    @if($setlist->event_date)
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $setlist->event_date->format('d.m.Y') }}</p>
                    @endif
                </div>
                <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $setlist->event_type === 'concert' ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700' }}">
                    {{ $setlist->event_type === 'concert' ? '🎤 Koncert' : '🎉 Zábava' }}
                </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ $setlist->setlist_songs_count }} piesní</p>
            <div class="flex gap-2">
                <a href="{{ route('setlists.show', $setlist) }}"
                   class="flex-1 text-center bg-amber-500 hover:bg-amber-600 text-slate-900 font-medium py-1.5 rounded-lg text-sm transition-colors">
                    Otvoriť
                </a>
                <a href="{{ route('setlists.export.csv', $setlist) }}"
                   style="padding:6px 10px;background:#16a34a;color:#fff;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background .15s;"
                   onmouseover="this.style.background='#15803d'"
                   onmouseout="this.style.background='#16a34a'"
                   title="Export CSV"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>CSV</a>
                @if($setlist->canBeDeletedBy(auth()->user()))
                <form method="POST" action="{{ route('setlists.destroy', $setlist) }}" class="inline delete-confirm-form" data-name="{{ $setlist->name }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-red-50 dark:hover:bg-red-950 hover:text-red-600 hover:border-red-300 text-sm transition-colors"
                            title="Zmazať">🗑</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@endif
<script>
document.querySelectorAll('.delete-confirm-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm('Zmazať playlist „' + form.dataset.name + '"?')) form.submit();
    });
});
</script>
@endsection
