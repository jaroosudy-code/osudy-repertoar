@extends('layouts.app')
@section('title', 'Nastavenia')

@section('content')
<div class="max-w-md mx-auto">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Nastavenia</h1>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-lg font-semibold text-slate-700 mb-1">Zmena hesla</h2>
        <p class="text-sm text-slate-400 mb-5">
            Po zmene oznam nove heslo ostatnym clenom kapely.
        </p>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('settings.password') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Aktualne heslo</label>
                <input type="password" name="current_password"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400
                              @error('current_password') border-red-400 @enderror">
                @error('current_password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nove heslo</label>
                <input type="password" name="new_password"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400
                              @error('new_password') border-red-400 @enderror">
                @error('new_password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Zopakuj nove heslo</label>
                <input type="password" name="new_password_confirmation"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold py-2.5 rounded-lg transition-colors">
                Zmenit heslo
            </button>
        </form>
    </div>

    <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-lg font-semibold text-slate-700 mb-3">Odhlasenie</h2>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="px-4 py-2 border border-slate-300 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                Odhlasit sa
            </button>
        </form>
    </div>
</div>
@endsection
