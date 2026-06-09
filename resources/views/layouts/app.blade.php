<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Repertoár') – Osudy</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PLAYLIST">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
    <style>
        @media (max-width: 639px) {
            .nav-settings-text { display: none; }
            #nav-inner { gap: 0.25rem; padding-left: 0.5rem; padding-right: 0.5rem; }
            #nav-inner .nav-logo { height: 2rem; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen font-sans">

<nav class="bg-slate-900 text-white shadow-lg">
    <div id="nav-inner" class="max-w-7xl mx-auto px-4 flex items-center gap-8 h-14">
        <a href="{{ route('songs.index') }}" class="shrink-0">
            <img src="/logo.gif" alt="Osudy" class="nav-logo h-10 w-auto">
        </a>
        <div class="flex gap-1">
            <a href="{{ route('songs.index') }}"
               class="px-3 py-1.5 rounded text-sm font-medium transition-colors
                      {{ request()->routeIs('songs.*') ? 'bg-amber-500 text-slate-900' : 'text-slate-300 hover:bg-slate-700' }}">
                Piesne
            </a>
            <a href="{{ route('setlists.index') }}"
               class="px-3 py-1.5 rounded text-sm font-medium transition-colors
                      {{ request()->routeIs('setlists.*') ? 'bg-amber-500 text-slate-900' : 'text-slate-300 hover:bg-slate-700' }}">
                Playlisty
            </a>
        </div>
        <div class="ml-auto flex gap-1">
            <a href="{{ route('settings') }}"
               class="px-3 py-1.5 rounded text-sm font-medium transition-colors
                      {{ request()->routeIs('settings*') ? 'bg-amber-500 text-slate-900' : 'text-slate-300 hover:bg-slate-700' }}">
                ⚙ <span class="nav-settings-text">Nastavenia</span>
            </a>
        </div>
    </div>
</nav>

<div id="offline-bar" style="display:none; background:#b45309; color:white; text-align:center; padding:6px; font-size:0.8rem;">
    ⚡ Offline režim — zobrazujú sa uložené stránky
</div>
<script>
    function updateOnlineStatus() {
        document.getElementById('offline-bar').style.display = navigator.onLine ? 'none' : 'block';
    }
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();
</script>

<main class="max-w-7xl mx-auto px-4 py-6">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg flex justify-between items-start">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-green-600 hover:text-green-800 font-bold">×</button>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

</body>
</html>
