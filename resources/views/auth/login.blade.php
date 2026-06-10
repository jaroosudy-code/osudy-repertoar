<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prihlásenie – Interný systém skupiny Osudy</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Repertoár">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @vite(['resources/css/app.css'])
    <script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <div class="mb-3">
            <img src="/logo.gif" alt="Osudy" class="h-14 w-auto mx-auto">
        </div>
        <h1 class="text-2xl font-bold text-white tracking-wide">INTERNÝ SYSTÉM SKUPINY</h1>
        <p class="text-slate-400 mt-1 text-sm">Prihlás sa svojím emailom</p>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                <input type="email" name="email" inputmode="email" autocomplete="email"
                       value="{{ old('email') }}" autofocus
                       class="w-full border border-slate-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-400
                              @error('email') border-red-400 @enderror"
                       placeholder="tvoj@email.sk">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Heslo</label>
                <input type="password" name="password" autocomplete="current-password"
                       class="w-full border border-slate-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-400
                              @error('password') border-red-400 @enderror"
                       placeholder="••••••••">
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center mb-6">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 accent-amber-500">
                <label for="remember" class="ml-2 text-sm text-slate-600">Zapamätať ma</label>
            </div>

            <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-slate-900 font-bold py-3.5 rounded-lg text-base transition-colors touch-manipulation">
                Prihlásiť sa
            </button>
        </form>
    </div>
</div>

</body>
</html>
