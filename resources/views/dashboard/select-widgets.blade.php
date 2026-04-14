<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Widgets Beheren – EcoCheck</title>
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
    {{-- Navigation --}}
    <x-navbar>
        <span class="text-white/20 text-sm">/</span>
        <span class="text-gray-400 text-sm">Widgets Beheren</span>
    </x-navbar>

    <main class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
        <div class="mb-10">
            <h1 class="text-3xl font-semibold text-white mb-2">Widgets Beheren</h1>
            <p class="text-gray-400">Kies welke widgets je op je dashboard wil weergeven. Je kunt zoveel widgets
                selecteren als je wilt.</p>
        </div>

        <form method="POST" action="{{ route('dashboard.save-widgets') }}" class="space-y-8">
            @csrf

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-500/20 bg-red-500/10 p-4">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-300">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($availableWidgets as $widget)
                    <div class="widget-card group border-2 border-white/10 rounded-xl p-6 cursor-pointer transition hover:border-blue-500/50 hover:bg-white/5 flex flex-col"
                        data-widget="{{ $widget['key'] }}">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                {!! \App\Models\UserDashboardWidget::getWidgetIcon($widget['icon']) !!}
                            </div>
                            <input type="checkbox" name="widgets[]" value="{{ $widget['key'] }}"
                                class="widget-checkbox w-5 h-5 rounded border-gray-500 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                @if (in_array($widget['key'], $selectedWidgets)) checked @endif>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">{{ $widget['name'] }}</h3>
                        <p class="text-sm text-gray-400 mb-6 flex-1">{{ $widget['description'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="flex gap-4 pt-6 border-t border-white/10">
                <a href="{{ route('dashboard') }}"
                    class="px-6 py-3 border border-white/20 rounded-lg text-white hover:bg-white/5 transition font-medium">
                    Terug naar Dashboard
                </a>
                <button type="submit"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition font-medium">
                    Wijzigingen Opslaan
                </button>
            </div>
        </form>
    </main>

    <script>
        // Toggle checkbox when clicking the card
        document.querySelectorAll('.widget-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox' && !e.target.closest('input')) {
                    const checkbox = this.querySelector('.widget-checkbox');
                    checkbox.checked = !checkbox.checked;
                    updateCardStyle(this);
                }
            });
        });

        // Update checkbox styling
        function updateCardStyle(card) {
            const checkbox = card.querySelector('.widget-checkbox');

            if (checkbox.checked) {
                card.classList.add('bg-blue-500/10', 'border-blue-500/50');
            } else {
                card.classList.remove('bg-blue-500/10', 'border-blue-500/50');
            }
        }

        // Initial styling
        document.querySelectorAll('.widget-card').forEach(card => {
            if (card.querySelector('.widget-checkbox').checked) {
                card.classList.add('bg-blue-500/10', 'border-blue-500/50');
            }
        });

        // Update checkbox listeners
        document.querySelectorAll('.widget-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateCardStyle(this.closest('.widget-card'));
            });
        });

        // User menu dropdown handled by navbar component
    </script>
</body>

</html>
