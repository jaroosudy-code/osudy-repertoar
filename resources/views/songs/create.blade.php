@extends('layouts.app')
@section('title', 'Nova piesne')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('songs.index') }}" class="text-slate-400 hover:text-slate-600">← Spat</a>
        <h1 class="text-2xl font-bold text-slate-800">Nova piesne</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="POST" action="{{ route('songs.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nazov piesne *</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                       placeholder="napr. Hotel California">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dlzka *</label>
                <input type="text" name="duration_formatted" value="{{ old('duration_formatted') }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono"
                       placeholder="3.45  alebo  3,45  alebo  3:45">
                <p class="text-xs text-slate-400 mt-1">Minúty a sekundy oddeľ bodkou, čiarkou alebo dvojbodkou</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Farba</label>
                @include('songs._color_palette', ['currentColor' => old('color', '#6366f1')])
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Tempo *</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tempo" value="fast" {{ old('tempo') === 'fast' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <span class="px-2 py-0.5 rounded-full text-sm bg-orange-100 text-orange-700">Rychla</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tempo" value="slow" {{ old('tempo') === 'slow' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <span class="px-2 py-0.5 rounded-full text-sm bg-blue-100 text-blue-700">Pomala</span>
                    </label>
                </div>
            </div>

            <div x-data="{ typ: '{{ old('type', '') }}' }">
                <label class="block text-sm font-medium text-slate-700 mb-2">Typ *</label>
                <div class="flex gap-4 mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="own" x-model="typ"
                               {{ old('type') === 'own' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <span class="px-2 py-0.5 rounded-full text-sm bg-green-100 text-green-700">Osudy</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="cover" x-model="typ"
                               {{ old('type') === 'cover' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <span class="px-2 py-0.5 rounded-full text-sm bg-purple-100 text-purple-700">Cover</span>
                    </label>
                </div>
                <div x-show="typ === 'cover'" x-transition style="display:none">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Interpret (original)</label>
                    <input type="text" name="original_artist" value="{{ old('original_artist') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                           placeholder="napr. Eagles, ABBA, Karel Gott...">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Autor textu</label>
                    <input type="text" name="author_lyrics" value="{{ old('author_lyrics') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                           placeholder="napr. Jan Novak">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Autor hudby</label>
                    <input type="text" name="author_music" value="{{ old('author_music') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                           placeholder="napr. Jan Novak">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Poznamky</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                          placeholder="Volitelne poznamky...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-5 py-2 rounded-lg transition-colors">
                    Ulozit piesne
                </button>
                <a href="{{ route('songs.index') }}"
                   class="px-5 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                    Zrusit
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
