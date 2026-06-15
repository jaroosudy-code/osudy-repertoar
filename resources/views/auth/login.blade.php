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
    <script>if (localStorage.getItem('darkMode') === '1') document.documentElement.classList.add('dark');</script>
    <script>if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
</head>
<body class="bg-slate-900 dark:bg-slate-950 min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <div class="mb-3">
            <img src="/logo.gif" alt="Osudy" class="h-14 w-auto mx-auto">
        </div>
        <h1 class="text-base font-bold text-white tracking-wide">MÁME NA JEDNOM MIESTE</h1>
        <p class="text-slate-400 mt-1 text-sm">Prihlás sa k ním svojím emailom</p>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-8">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email</label>
                <input type="email" name="email" inputmode="email" autocomplete="email"
                       value="{{ old('email') }}" autofocus
                       class="w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-400
                              @error('email') border-red-400 @enderror"
                       placeholder="tvoj@email.sk">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Heslo</label>
                <div class="flex items-center border border-slate-300 dark:border-slate-600 rounded-lg focus-within:ring-2 focus-within:ring-amber-400 @error('password') border-red-400 @enderror">
                    <input type="password" name="password" id="pw-login" autocomplete="current-password"
                           class="flex-1 px-4 py-3 text-base bg-transparent dark:text-slate-100 outline-none rounded-l-lg"
                           placeholder="••••••••">
                    <button type="button" onclick="togglePw('pw-login', this)"
                            class="px-3 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 outline-none focus:outline-none transition-colors shrink-0"
                            tabindex="-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path class="eye-open" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                            <circle class="eye-open" cx="12" cy="12" r="3"/>
                            <path class="eye-shut" style="display:none" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line class="eye-shut" style="display:none" x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center mb-6">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 accent-amber-500">
                <label for="remember" class="ml-2 text-sm text-slate-600 dark:text-slate-400">Zapamätať ma</label>
            </div>

            <button type="submit"
                    style="width:100%; padding:15px; border:none; border-radius:14px; cursor:pointer;
                           background:linear-gradient(180deg,#fbbf24 0%,#f59e0b 100%);
                           box-shadow:0 4px 18px rgba(245,158,11,0.45);
                           color:#0f172a; font-weight:800; font-size:0.8rem; letter-spacing:0.2em;
                           transition:all 0.15s ease;"
                    onmouseover="this.style.background='linear-gradient(180deg,#f59e0b 0%,#d97706 100%)';this.style.boxShadow='0 6px 24px rgba(245,158,11,0.6)';this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.background='linear-gradient(180deg,#fbbf24 0%,#f59e0b 100%)';this.style.boxShadow='0 4px 18px rgba(245,158,11,0.45)';this.style.transform='translateY(0)'">
                VOJDI
            </button>
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
</body>
</html>
