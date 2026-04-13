<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selecteer Dashboard Widgets – EcoCheck</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-[#0b0f1a] text-gray-100 min-h-screen">
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
                    <span class="font-semibold text-white">EcoCheck</span>
                </div>
                <div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1.5 text-xs font-medium text-gray-400 hover:text-white border border-white/10 hover:border-white/20 rounded-md transition">
                            Uitloggen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-white mb-2">Selecteer Dashboard Widgets</h1>
            <p class="text-gray-400">Kies welke widgets je op je dashboard wil zien. Je kunt er zoveel selecteren als je
                wilt.</p>
        </div>

        <form method="POST" action="{{ route('dashboard.save-widgets') }}" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($availableWidgets as $widget)
                    <div class="widget-card border-2 border-white/10 rounded-lg p-6 cursor-pointer transition hover:border-blue-500/50 hover:bg-white/5"
                        data-widget="{{ $widget['key'] }}">
                        <div class="flex items-start gap-4">
                            <div class="text-4xl">{{ $widget['icon'] }}</div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-white mb-1">{{ $widget['name'] }}</h3>
                                <p class="text-sm text-gray-400 mb-4">{{ $widget['description'] }}</p>
                                <input type="checkbox" name="widgets[]" value="{{ $widget['key'] }}"
                                    class="widget-checkbox w-5 h-5 rounded border-gray-500 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                    @if (in_array($widget['key'], $selectedWidgets)) checked @endif>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex gap-4">
                <a href="{{ route('dashboard') }}"
                    class="px-6 py-3 border border-white/20 rounded-lg text-white hover:bg-white/5 transition">
                    Annuleren
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition">
                    Opslaan & Terug naar Dashboard
                </button>
            </div>
        </form>
    </main>

    <script>
        // Toggle checkbox when clicking the card
        document.querySelectorAll('.widget-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = this.querySelector('.widget-checkbox');
                    checkbox.checked = !checkbox.checked;
                }
                this.classList.toggle('bg-blue-500/10', this.querySelector('.widget-checkbox').checked);
                this.classList.toggle('border-blue-500/50', this.querySelector('.widget-checkbox').checked);
            });
        });

        // Initial styling
        document.querySelectorAll('.widget-card').forEach(card => {
            if (card.querySelector('.widget-checkbox').checked) {
                card.classList.add('bg-blue-500/10', 'border-blue-500/50');
            }
        });
    </script>
</body>

</html>
