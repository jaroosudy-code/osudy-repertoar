@extends('layouts.app')
@section('title', 'Čakáš na priradenie')

@section('content')
<div class="max-w-md mx-auto mt-16 text-center">
    <div class="text-5xl mb-4">🎵</div>
    <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Ešte nie si priradený ku kapele</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">
        Počkaj, kým ťa admin zaradí do niektorej kapely. Ak si myslíš, že ide o chybu, kontaktuj správcu aplikácie.
    </p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
                class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-sm transition-colors">
            Odhlásiť sa
        </button>
    </form>
</div>
@endsection
