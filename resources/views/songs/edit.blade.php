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
                <label class="block text-sm font-medium text-slate-700 mb-1">Text s akordmi</label>
                <p class="text-xs text-slate-400 mb-2">Akordy vkladaj priamo do textu vo formáte <code class="bg-slate-100 px-1 rounded">&lt;E&gt;</code> <code class="bg-slate-100 px-1 rounded">&lt;F#mi&gt;</code> <code class="bg-slate-100 px-1 rounded">&lt;H7&gt;</code></p>
                <textarea name="lyrics" rows="12"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono text-sm">{{ old('lyrics', $song->lyrics) }}</textarea>
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
@endsection
