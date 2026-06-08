@extends('layouts.app')
@section('title', 'Nový setlist')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('setlists.index') }}" class="text-slate-400 hover:text-slate-600">← Späť</a>
        <h1 class="text-2xl font-bold text-slate-800">Nový setlist</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="POST" action="{{ route('setlists.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Názov setlistu *</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                       placeholder="napr. Svadba Novákových 2024">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Typ podujatia *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="event_type" value="concert" class="sr-only peer"
                               {{ old('event_type') === 'concert' ? 'checked' : '' }}>
                        <div class="border-2 rounded-xl p-4 text-center transition-colors
                                    peer-checked:border-amber-500 peer-checked:bg-amber-50 border-slate-200 hover:border-slate-300">
                            <div class="text-3xl mb-1">🎤</div>
                            <div class="font-semibold text-slate-800">Koncert</div>
                            <div class="text-xs text-slate-500 mt-1">Jeden plynulý program</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="event_type" value="entertainment" class="sr-only peer"
                               {{ old('event_type') === 'entertainment' ? 'checked' : '' }}>
                        <div class="border-2 rounded-xl p-4 text-center transition-colors
                                    peer-checked:border-amber-500 peer-checked:bg-amber-50 border-slate-200 hover:border-slate-300">
                            <div class="text-3xl mb-1">🎉</div>
                            <div class="font-semibold text-slate-800">Zábava</div>
                            <div class="text-xs text-slate-500 mt-1">Kolá s prestávkami</div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dátum podujatia</label>
                <input type="date" name="event_date" value="{{ old('event_date') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Poznámky</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                          placeholder="Voliteľné poznámky…">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-5 py-2 rounded-lg transition-colors">
                    Vytvoriť setlist
                </button>
                <a href="{{ route('setlists.index') }}"
                   class="px-5 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                    Zrušiť
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
