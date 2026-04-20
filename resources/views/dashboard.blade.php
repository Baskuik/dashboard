<!DOCTYPE html>
<html lang="nl" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard – EcoCheck</title>
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

        .stat-card {
            transition: border-color 0.15s ease, background 0.15s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            background: rgba(59, 130, 246, 0.08) !important;
        }

        /* Light mode styles */
        html:not(.dark) {
            color-scheme: light;
        }

        html:not(.dark) body {
            @apply bg-gray-50 text-gray-900;
        }

        html:not(.dark) input::placeholder,
        html:not(.dark) textarea::placeholder {
            @apply text-gray-400;
        }

        /* Dark mode styles (default) */
        html.dark {
            color-scheme: dark;
        }

        html.dark body {
            @apply bg-[#0b0f1a] text-gray-100;
        }
    </style>
</head>

<body class="bg-[#0b0f1a] dark:bg-[#050a14] dark:text-gray-100 text-gray-900 min-h-screen">
    <!-- Theme Initialization -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            const html = document.documentElement;
            if (theme === 'light') {
                html.classList.remove('dark');
                document.body.classList.add('bg-[#f5f5f5]');
                document.body.classList.remove('bg-[#050a14]');
            } else {
                html.classList.add('dark');
                document.body.classList.remove('bg-[#f5f5f5]');
                document.body.classList.add('bg-[#050a14]');
            }
        })();
    </script>
    <!-- Theme Initialization -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            const html = document.documentElement;
            if (theme === 'light') {
                html.classList.remove('dark');
            } else {
                html.classList.add('dark');
            }
        })();
    </script>

    {{-- Navigation --}}
    <x-navbar>
        <span class="text-white/20 dark:text-white/20 text-sm">/</span>
        <span class="text-gray-400 dark:text-gray-400 text-sm">Dashboard</span>
    </x-navbar>

    <main class="max-w-7xl mx-auto px-6 lg:px-8 py-10">

        {{-- Page header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-white">Overzicht</h1>
            <p class="text-sm text-gray-500 mt-1">Milieu-check registraties en statistieken</p>
        </div>

        {{-- Upload Section --}}
        <div class="border border-white/8 bg-[#131928] rounded-xl p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-white">Excel-bestand uploaden</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Importeer milieu-check records (.xlsx, .xls – max 10 MB)</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                    <p class="text-xs text-green-400">✓ {{ session('success') }}</p>
                </div>
            @endif

            <form action="/upload" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex gap-3 items-center">
                    <label for="file"
                        class="flex-1 flex items-center gap-3 px-4 py-3 border border-dashed border-white/15 rounded-lg cursor-pointer hover:border-blue-500/40 hover:bg-blue-500/5 transition">
                        <svg class="w-5 h-5 text-blue-400 shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span id="file-label" class="text-sm text-gray-400">Klik om bestand te selecteren</span>
                        <input id="file" type="file" class="hidden" name="file" accept=".xlsx,.xls" required
                            onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? 'Klik om bestand te selecteren'" />
                    </label>
                    <button type="submit"
                        class="shrink-0 px-5 py-3 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg transition">
                        Uploaden
                    </button>
                </div>
            </form>
        </div>

        {{-- Stats --}}
        @include('dashboard.stats', ['stats' => $stats])

        {{-- Charts --}}
        @include('dashboard.charts', ['chartData' => $chartData, 'kostenPerMaand' => $kostenPerMaand])

        {{-- Records table --}}
        @include('dashboard.records-table', ['paginatedRecords' => $paginatedRecords])

    </main>
</body>

</html>
