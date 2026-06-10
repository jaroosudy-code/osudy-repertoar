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
                <input type="password" name="current_password" autocomplete="current-password"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('current_password') border-red-400 @enderror">
                @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nové heslo</label>
                    <input type="password" name="new_password" autocomplete="new-password"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('new_password') border-red-400 @enderror">
                    @error('new_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Zopakuj nové heslo</label>
                    <input type="password" name="new_password_confirmation" autocomplete="new-password"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
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
@endsection
