<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }} – EcoCheck</title>
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

    {{-- Nav --}}
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
                    <span class="text-white/20 text-sm">/</span>
                    <a href="{{ route('dashboard') }}"
                        class="text-gray-400 text-sm hover:text-gray-200 transition">Dashboard</a>
                    <span class="text-white/20 text-sm">/</span>
                    <span class="text-gray-400 text-sm">{{ $pageTitle }}</span>
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        <span class="text-sm text-gray-400 mono">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium text-gray-400 hover:text-white border border-white/10 hover:border-white/20 rounded-md transition">
                                Uitloggen
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 lg:px-8 py-10">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('dashboard') }}"
                class="p-2 border border-white/10 rounded-lg hover:border-white/20 hover:bg-white/5 transition">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-white">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $pageSubtitle ?? '' }}</p>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="mb-8">
            <form method="GET" class="flex gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" placeholder="Zoeken op actie of medewerker..."
                        value="{{ $search ?? '' }}"
                        class="w-full pl-10 pr-4 py-2.5 bg-[#131928] border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition">
                </div>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg transition">
                    Zoeken
                </button>
                @if ($search)
                    <a href="{{ request()->url() }}"
                        class="px-6 py-2.5 bg-gray-700/50 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition border border-gray-600/50">
                        Wissen
                    </a>
                @endif
            </form>
        </div>

        {{-- Grouped records --}}
        @forelse($groups as $groupName => $records)
            <div class="border border-white/8 bg-[#131928] rounded-xl p-5 mb-4">

                {{-- Group header --}}
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/15 flex items-center justify-center">
                            <span class="text-blue-300 text-xs font-semibold">
                                {{ strtoupper(substr($groupName, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-white text-sm">{{ $groupName }}</p>
                            <p class="text-xs text-gray-500">{{ $records->count() }}
                                {{ $records->count() === 1 ? 'record' : 'records' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-cyan-400 mono">€
                            {{ number_format($records->sum('costs'), 2, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">{{ number_format($records->sum('time'), 1, ',', '.') }}u
                            totaal</p>
                    </div>
                </div>

                {{-- Records table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/[0.04]">
                                <th
                                    class="pb-2 pr-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Datum</th>
                                <th
                                    class="pb-2 pr-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Actie</th>
                                <th
                                    class="pb-2 pr-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Omschrijving</th>
                                <th
                                    class="pb-2 pr-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Medewerker</th>
                                <th
                                    class="pb-2 pr-4 text-right text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Uren</th>
                                <th
                                    class="pb-2 text-right text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Kosten</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.03]">
                            @foreach ($records as $record)
                                <tr class="hover:bg-white/[0.02] transition">
                                    <td class="py-2.5 pr-4 text-gray-400 mono whitespace-nowrap text-xs">
                                        {{ $record->date ? \Carbon\Carbon::parse($record->date)->format('d-m-Y') : '–' }}
                                    </td>
                                    <td class="py-2.5 pr-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/15 text-blue-300 border border-blue-500/20">
                                            {{ $record->action }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-4 text-gray-400 max-w-xs text-xs">
                                        <span title="{{ $record->description }}">
                                            {{ Str::limit($record->description, 70) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 pr-4 text-gray-300 whitespace-nowrap text-xs">
                                        {{ $record->worker }}</td>
                                    <td class="py-2.5 pr-4 text-right text-gray-400 mono text-xs whitespace-nowrap">
                                        {{ number_format($record->time, 1, ',', '.') }}u
                                    </td>
                                    <td
                                        class="py-2.5 text-right font-medium text-cyan-400 mono text-xs whitespace-nowrap">
                                        € {{ number_format($record->costs, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="border border-white/8 bg-[#131928] rounded-xl p-16 text-center">
                <p class="text-gray-600 text-sm">Geen records gevonden.</p>
                <a href="{{ route('dashboard') }}"
                    class="text-blue-400 text-sm hover:text-blue-300 transition mt-2 inline-block">
                    Terug naar dashboard
                </a>
            </div>
        @endforelse

    </main>
</body>

</html>
