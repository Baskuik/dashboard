<!DOCTYPE html>
<html lang="nl" class="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'DM Sans', sans-serif;
        }

        .mono {
            font-family: 'DM Mono', monospace;
        }
    </style>
</head>

<body class="bg-[#162a4a] text-gray-100 min-h-screen">
    <!-- Custom Navbar (Dashboard Style) -->
    <nav class="border-b border-gray-200 dark:border-white/5 bg-white dark:bg-[#1e3a5f] sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-blue-500 rounded flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="font-semibold text-gray-900 dark:text-white tracking-tight">EcoCheck</span>
                </div>
                <div class="flex items-center gap-3 relative">
                    @auth
                        <button type="button" id="user-menu-btn"
                            class="flex items-center gap-2 px-3 py-1.5 text-gray-900 dark:text-white text-sm font-semibold hover:bg-gray-100 dark:hover:bg-white/10 rounded-md transition">
                            <span class="mono">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </button>

                        <div id="user-menu-dropdown"
                            class="hidden absolute top-full right-0 mt-2 bg-white dark:bg-[#131928] border border-gray-200 dark:border-white/10 rounded-lg shadow-2xl z-50 w-48 py-1">
                            <a href="{{ route('dashboard.select-widgets') }}"
                                class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition">
                                Widgets beheren
                            </a>
                            @if (Auth::user()->is_admin)
                                <a href="{{ route('filament.admin.pages.dashboard') }}"
                                    class="block px-4 py-2.5 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition font-semibold">
                                    🔐 Admin panel
                                </a>
                                <hr class="border-gray-100 dark:border-white/5 my-1">
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition">
                                    Uitloggen
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Filament Content -->
    {{ $slot }}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuBtn = document.getElementById('user-menu-btn');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');

            if (userMenuBtn && userMenuDropdown) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userMenuDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', function(e) {
                    if (!userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                        userMenuDropdown.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>

</html>
