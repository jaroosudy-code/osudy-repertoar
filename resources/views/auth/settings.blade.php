@extends('layouts.app')
@section('title', 'Nastavenia')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-6">Nastavenia</h1>

<div class="space-y-6 max-w-2xl">

    {{-- Profil --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center gap-5">
        <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center text-2xl font-bold text-amber-700 shrink-0">
            {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div>
            <p class="font-semibold text-slate-800 text-lg leading-tight">{{ auth()->user()->name }}</p>
            <p class="text-sm text-slate-500 mt-0.5">{{ auth()->user()->email }}</p>
            @if(auth()->user()->role)
                <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium {{ auth()->user()->role->badgeClass() }}">
                    {{ auth()->user()->role->name }}
                </span>
            @endif
        </div>
    </div>

    {{-- Zmena hesla --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-base font-semibold text-slate-700 mb-5">Zmena hesla</h2>

        @if(session('success'))
            <div class="mb-5 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('settings.password') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Aktuálne heslo</label>
                <div class="flex items-center border border-slate-300 rounded-lg focus-within:ring-2 focus-within:ring-amber-400 @error('current_password') border-red-400 @enderror">
                    <input type="password" name="current_password" id="pw-cur" autocomplete="current-password"
                           class="flex-1 px-3 py-2.5 text-sm bg-transparent outline-none rounded-l-lg">
                    <button type="button" onclick="togglePw('pw-cur', this)" tabindex="-1"
                            class="px-2.5 text-slate-400 hover:text-slate-600 outline-none focus:outline-none transition-colors shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path class="eye-open" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                            <circle class="eye-open" cx="12" cy="12" r="3"/>
                            <path class="eye-shut" style="display:none" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line class="eye-shut" style="display:none" x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nové heslo</label>
                    <div class="flex items-center border border-slate-300 rounded-lg focus-within:ring-2 focus-within:ring-amber-400 @error('new_password') border-red-400 @enderror">
                        <input type="password" name="new_password" id="pw-new" autocomplete="new-password"
                               class="flex-1 px-3 py-2.5 text-sm bg-transparent outline-none rounded-l-lg">
                        <button type="button" onclick="togglePw('pw-new', this)" tabindex="-1"
                                class="px-2.5 text-slate-400 hover:text-slate-600 outline-none focus:outline-none transition-colors shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path class="eye-open" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                <circle class="eye-open" cx="12" cy="12" r="3"/>
                                <path class="eye-shut" style="display:none" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line class="eye-shut" style="display:none" x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('new_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Zopakuj nové heslo</label>
                    <div class="flex items-center border border-slate-300 rounded-lg focus-within:ring-2 focus-within:ring-amber-400">
                        <input type="password" name="new_password_confirmation" id="pw-rep" autocomplete="new-password"
                               class="flex-1 px-3 py-2.5 text-sm bg-transparent outline-none rounded-l-lg">
                        <button type="button" onclick="togglePw('pw-rep', this)" tabindex="-1"
                                class="px-2.5 text-slate-400 hover:text-slate-600 outline-none focus:outline-none transition-colors shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path class="eye-open" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                <circle class="eye-open" cx="12" cy="12" r="3"/>
                                <path class="eye-shut" style="display:none" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line class="eye-shut" style="display:none" x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-1">
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                    Zmeniť heslo
                </button>
            </div>
        </form>
    </div>

</div>
<script>
function togglePw(fieldId, btn) {
    const inp = document.getElementById(fieldId);
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.querySelectorAll('.eye-open').forEach(el => el.style.display = show ? 'none' : '');
    btn.querySelectorAll('.eye-shut').forEach(el => el.style.display = show ? '' : 'none');
}
</script>
@endsection
