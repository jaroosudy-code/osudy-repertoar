@extends('layouts.app')
@section('title', 'Setlisty')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Setlisty</h1>
    <a href="{{ route('setlists.create') }}"
       class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-lg transition-colors">
        + Nový setlist
    </a>
</div>

@if($setlists->isEmpty())
    <div class="text-center py-16 text-slate-400">
        <div class="text-5xl mb-3">📋</div>
        <p class="text-lg">Zatiaľ žiadne setlisty. <a href="{{ route('setlists.create') }}" class="text-amber-500 hover:underline">Vytvor prvý!</a></p>
    </div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($setlists as $setlist)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h2 class="font-bold text-slate-800 text-lg leading-tight">{{ $setlist->name }}</h2>
                    @if($setlist->event_date)
                        <p class="text-sm text-slate-500 mt-0.5">{{ $setlist->event_date->format('d.m.Y') }}</p>
                    @endif
                </div>
                <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $setlist->event_type === 'concert' ? 'bg-indigo-100 text-indigo-700' : 'bg-pink-100 text-pink-700' }}">
                    {{ $setlist->event_type === 'concert' ? '🎤 Koncert' : '🎉 Zábava' }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mb-4">{{ $setlist->setlist_songs_count }} piesní</p>
            <div class="flex gap-2">
                <a href="{{ route('setlists.show', $setlist) }}"
                   class="flex-1 text-center bg-amber-500 hover:bg-amber-600 text-slate-900 font-medium py-1.5 rounded-lg text-sm transition-colors">
                    Otvoriť
                </a>
                <a href="{{ route('setlists.export.csv', $setlist) }}"
                   class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm transition-colors"
                   title="Export CSV">⬇ CSV</a>
                <form method="POST" action="{{ route('setlists.destroy', $setlist) }}" class="inline"
                      onsubmit="return confirm('Zmazať setlist „{{ $setlist->name }}"?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-300 text-sm transition-colors"
                            title="Zmazať">🗑</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
