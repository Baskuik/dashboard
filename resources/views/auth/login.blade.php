<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inloggen – EcoCheck</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>body { font-family: 'DM Sans', sans-serif; } .mono { font-family: 'DM Mono', monospace; }</style>
</head>
<body class="bg-[#0b0f1a] text-gray-100 min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="flex items-center gap-2.5 mb-8">
            <div class="w-7 h-7 bg-blue-500 rounded flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <span class="font-semibold text-white">EcoCheck</span>
        </div>

        {{-- Card --}}
        <div class="border border-white/8 bg-[#131928] rounded-xl p-7">
            <h1 class="text-lg font-semibold text-white mb-1">Inloggen</h1>
            <p class="text-sm text-gray-500 mb-6">Voer je gegevens in om verder te gaan</p>

            @if ($errors->any())
                <div class="mb-5 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-400 mb-1.5">E-mailadres</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        class="w-full px-3 py-2.5 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-100 placeholder-gray-600
                               focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition"
                        placeholder="naam@bedrijf.nl" required autocomplete="email">
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-gray-400 mb-1.5">Wachtwoord</label>
                    <input type="password" id="password" name="password"
                        class="w-full px-3 py-2.5 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-100 placeholder-gray-600
                               focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition"
                        placeholder="••••••••" required autocomplete="current-password">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="remember" name="remember"
                        class="w-3.5 h-3.5 rounded border-white/20 bg-white/5 accent-blue-500">
                    <label for="remember" class="text-xs text-gray-500">Onthoud mij</label>
                </div>

                <button type="submit"
                    class="w-full mt-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg transition">
                    Inloggen
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-white/5 space-y-4">
                <p class="text-center text-xs text-gray-500">
                    Nog geen account?
                    <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 transition">Registreren</a>
                </p>
                <div class="bg-white/[0.03] border border-white/8 rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-600 mb-1.5">Demo-account</p>
                    <p class="text-xs mono text-cyan-400">admin1@gmail.com</p>
                    <p class="text-xs mono text-cyan-400">ditiseentest1234</p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>