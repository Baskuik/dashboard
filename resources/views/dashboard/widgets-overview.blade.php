<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Widgets Overzicht – EcoCheck</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
        }

        .mono {
            font-family: 'DM Mono', monospace;
        }
    </style>
</head>

<body class="bg-[#0b0f1a] text-gray-100 min-h-screen">
    {{-- Navigation --}}
    <nav class="border-b border-white/5 bg-[#0f1421]/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-blue-500 rounded flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="font-semibold text-white tracking-tight">EcoCheck</span>
                    <span class="text-white/20 text-sm">/</span>
                    <span class="text-gray-400 text-sm">Widgets Overzicht</span>
                </div>
                <div class="flex items-center gap-3 relative">
                    @auth
                        <button type="button" id="user-menu-btn"
                            class="flex items-center gap-2 px-3 py-1.5 text-white text-sm font-semibold hover:bg-white/10 rounded-md transition">
                            <span class="mono">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </button>

                        <div id="user-menu-dropdown"
                            class="hidden absolute top-full right-0 mt-2 bg-[#131928] border border-white/10 rounded-lg shadow-2xl z-50 w-48 py-1">
                            <a href="{{ route('dashboard.select-widgets') }}"
                                class="block px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                                Widgets Beheren
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                                    Uitloggen
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-3 py-1.5 text-xs font-medium text-blue-400 hover:text-blue-300 border border-blue-500/30 rounded-md transition">
                            Inloggen
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
        <div class="mb-10">
            <h1 class="text-3xl font-semibold text-white mb-2">Widgets Overzicht</h1>
            <p class="text-gray-400">Volledig overzicht van alle beschikbare dashboard widgets. Hier zie je alle
                mogelijke widgets die je op je dashboard kunt selecteren.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($widgets as $widget)
                <div class="border border-white/10 rounded-xl p-6 hover:border-white/20 transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            {!! \App\Models\UserDashboardWidget::getWidgetIcon($widget['icon']) !!}
                        </div>
                        @if (in_array($widget['key'], $activeWidgets))
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                                Actief
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-500/20 text-gray-300 border border-gray-500/30">
                                Inactief
                            </span>
                        @endif
                    </div>

                    <h3 class="text-lg font-semibold text-white mb-2">{{ $widget['name'] }}</h3>
                    <p class="text-sm text-gray-400 mb-6">{{ $widget['description'] }}</p>

                    <div class="px-4 py-3 bg-white/5 rounded-lg border border-white/10 mb-6">
                        <p class="text-xs text-gray-500 font-semibold mb-2">Widget ID</p>
                        <p class="text-xs text-gray-300 mono">{{ $widget['key'] }}</p>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-white/10">
                        @if (in_array($widget['key'], $activeWidgets))
                            <div class="flex-1">
                                <p class="text-xs text-green-400 font-medium">✓ Zichtbaar op dashboard</p>
                            </div>
                        @else
                            <div class="flex-1">
                                <p class="text-xs text-gray-500">Voeg toe via Widgets Beheren</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex gap-4 pt-8 border-t border-white/10 mt-10">
            <a href="{{ route('dashboard') }}"
                class="px-6 py-3 border border-white/20 rounded-lg text-white hover:bg-white/5 transition font-medium">
                Terug naar Dashboard
            </a>
            <a href="{{ route('dashboard.select-widgets') }}"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition font-medium">
                Widgets Beheren
            </a>
        </div>
    </main>

    <script>
        // User menu dropdown toggle
        const userMenuBtn = document.getElementById('user-menu-btn');
        const userMenuDropdown = document.getElementById('user-menu-dropdown');

        if (userMenuBtn && userMenuDropdown) {
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenuDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                    userMenuDropdown.classList.add('hidden');
                }
            });
        }
    </script>
</body>

</html>
