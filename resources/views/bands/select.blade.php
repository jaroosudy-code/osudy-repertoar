@extends('layouts.app')
@section('title', 'Vybrať kapelu')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-2">Vyber kapelu</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Si členom viacerých kapiel. Vyber s ktorou chceš pracovať.</p>

    <div class="space-y-3">
        @foreach($bands as $band)
        <form method="POST" action="{{ route('bands.switch', $band) }}">
            @csrf
            <button type="submit"
                    class="w-full text-left bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-amber-400 hover:bg-amber-50 dark:hover:bg-slate-700 rounded-xl px-5 py-4 transition-all group">
                <div class="font-semibold text-slate-800 dark:text-slate-100 group-hover:text-amber-700 dark:group-hover:text-amber-400">
                    {{ $band->name }}
                </div>
                @if($band->description)
                <div class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $band->description }}</div>
                @endif
            </button>
        </form>
        @endforeach
    </div>
</div>
@endsection
