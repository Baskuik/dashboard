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

    {{-- GSAP Animation Library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
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
    <x-navbar>
        <span class="text-white/20 text-sm">/</span>
        <span class="text-gray-400 text-sm">Widgets Overzicht</span>
    </x-navbar>

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
        // GSAP Animations - Widgets Overview
        document.addEventListener('DOMContentLoaded', function() {
            // Animate widget overview cards
            gsap.to('.grid > div', {
                duration: 0.6,
                opacity: 1,
                y: 0,
                stagger: 0.1,
                ease: 'power2.out',
            });
            gsap.set('.grid > div', {
                opacity: 0,
                y: 20
            });

            // Hover animation for widget cards
            document.querySelectorAll('.grid > div').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    gsap.to(this, {
                        duration: 0.3,
                        scale: 1.03,
                        borderColor: 'rgba(59, 130, 246, 0.5)',
                        ease: 'power2.out',
                    });
                });
                card.addEventListener('mouseleave', function() {
                    gsap.to(this, {
                        duration: 0.3,
                        scale: 1,
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        ease: 'power2.out',
                    });
                });
            });
        });
    </script>
</body>

</html>
