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

    {{-- Nav --}}
    <x-navbar>
        <span class="text-white/20 text-sm">/</span>
        <a href="{{ route('dashboard') }}?{{ http_build_query(request()->query()) }}"
            class="text-gray-400 text-sm hover:text-gray-200 transition">Dashboard</a>
        <span class="text-white/20 text-sm">/</span>
        <span class="text-gray-400 text-sm">{{ $pageTitle }}</span>
    </x-navbar>

    <main class="max-w-7xl mx-auto px-6 lg:px-8 py-10">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('dashboard') }}?{{ http_build_query(request()->query()) }}"
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
            <form method="GET" class="space-y-4" id="filter-form">
                <!-- Search Row -->
                <div class="flex gap-3">
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
                </div>

                <!-- Date Range Row with Custom Calendar -->
                <div class="flex gap-3 items-end">
                    <!-- From Date Picker -->
                    <div class="flex-1 relative">
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Van</label>
                        <div class="relative">
                            <input type="hidden" name="from_date" id="from_date_hidden" value="{{ $fromDate ?? '' }}">
                            <button type="button" id="from-date-btn"
                                class="w-full px-4 py-2.5 bg-[#131928] border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition flex items-center justify-between hover:border-white/20">
                                <span
                                    id="from-date-display">{{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M Y') : 'Selecteer datum' }}</span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <!-- Popup Calendar From -->
                            <div id="from-calendar"
                                class="hidden absolute top-full left-0 mt-2 bg-[#131928] border border-white/10 rounded-lg shadow-2xl z-50 p-4 w-80">
                                <div class="flex items-center justify-between mb-4">
                                    <button type="button" id="from-month-year-btn"
                                        class="flex items-center gap-1 px-3 py-1.5 text-white text-sm font-semibold hover:bg-white/10 rounded transition">
                                        <span id="from-month-year">April 2026</span>
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                    </button>

                                    <!-- Month/Year Picker Dropdown -->
                                    <div id="from-month-year-picker"
                                        class="hidden absolute top-full left-0 mt-2 bg-[#0f1421] border border-white/10 rounded-lg shadow-lg z-50 p-3">
                                        <div class="mb-3">
                                            <p class="text-xs text-gray-400 mb-2 font-semibold">Maand</p>
                                            <div class="grid grid-cols-3 gap-1">
                                                <button type="button" data-month="0"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Jan</button>
                                                <button type="button" data-month="1"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Feb</button>
                                                <button type="button" data-month="2"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Mrt</button>
                                                <button type="button" data-month="3"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Apr</button>
                                                <button type="button" data-month="4"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Mei</button>
                                                <button type="button" data-month="5"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Jun</button>
                                                <button type="button" data-month="6"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Jul</button>
                                                <button type="button" data-month="7"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Aug</button>
                                                <button type="button" data-month="8"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Sep</button>
                                                <button type="button" data-month="9"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Okt</button>
                                                <button type="button" data-month="10"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Nov</button>
                                                <button type="button" data-month="11"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Dec</button>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 mb-2 font-semibold">Jaar</p>
                                            <div class="flex gap-2 items-center justify-between">
                                                <button type="button" id="from-year-prev"
                                                    class="p-1 hover:bg-white/10 rounded transition">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                                <span id="from-year-display"
                                                    class="text-sm font-semibold text-white min-w-[50px] text-center">2026</span>
                                                <button type="button" id="from-year-next"
                                                    class="p-1 hover:bg-white/10 rounded transition">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-1">
                                        <button type="button" class="p-1 hover:bg-white/10 rounded transition"
                                            id="from-prev-month">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <button type="button" class="p-1 hover:bg-white/10 rounded transition"
                                            id="from-next-month">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-500 mb-2">
                                    <div>Ma</div>
                                    <div>Di</div>
                                    <div>Wo</div>
                                    <div>Do</div>
                                    <div>Vr</div>
                                    <div>Za</div>
                                    <div>Zo</div>
                                </div>
                                <div id="from-days" class="grid grid-cols-7 gap-1"></div>
                            </div>
                        </div>
                    </div>

                    <!-- To Date Picker -->
                    <div class="flex-1 relative">
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Tot</label>
                        <div class="relative">
                            <input type="hidden" name="to_date" id="to_date_hidden" value="{{ $toDate ?? '' }}">
                            <button type="button" id="to-date-btn"
                                class="w-full px-4 py-2.5 bg-[#131928] border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition flex items-center justify-between hover:border-white/20">
                                <span
                                    id="to-date-display">{{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M Y') : 'Selecteer datum' }}</span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <!-- Popup Calendar To -->
                            <div id="to-calendar"
                                class="hidden absolute top-full left-0 mt-2 bg-[#131928] border border-white/10 rounded-lg shadow-2xl z-50 p-4 w-80">
                                <div class="flex items-center justify-between mb-4">
                                    <button type="button" id="to-month-year-btn"
                                        class="flex items-center gap-1 px-3 py-1.5 text-white text-sm font-semibold hover:bg-white/10 rounded transition">
                                        <span id="to-month-year">April 2026</span>
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                    </button>

                                    <!-- Month/Year Picker Dropdown -->
                                    <div id="to-month-year-picker"
                                        class="hidden absolute top-full left-0 mt-2 bg-[#0f1421] border border-white/10 rounded-lg shadow-lg z-50 p-3">
                                        <div class="mb-3">
                                            <p class="text-xs text-gray-400 mb-2 font-semibold">Maand</p>
                                            <div class="grid grid-cols-3 gap-1">
                                                <button type="button" data-month="0"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Jan</button>
                                                <button type="button" data-month="1"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Feb</button>
                                                <button type="button" data-month="2"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Mrt</button>
                                                <button type="button" data-month="3"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Apr</button>
                                                <button type="button" data-month="4"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Mei</button>
                                                <button type="button" data-month="5"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Jun</button>
                                                <button type="button" data-month="6"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Jul</button>
                                                <button type="button" data-month="7"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Aug</button>
                                                <button type="button" data-month="8"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Sep</button>
                                                <button type="button" data-month="9"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Okt</button>
                                                <button type="button" data-month="10"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Nov</button>
                                                <button type="button" data-month="11"
                                                    class="month-btn text-xs py-1 px-2 rounded hover:bg-blue-600/50 transition">Dec</button>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 mb-2 font-semibold">Jaar</p>
                                            <div class="flex gap-2 items-center justify-between">
                                                <button type="button" id="to-year-prev"
                                                    class="p-1 hover:bg-white/10 rounded transition">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                                <span id="to-year-display"
                                                    class="text-sm font-semibold text-white min-w-[50px] text-center">2026</span>
                                                <button type="button" id="to-year-next"
                                                    class="p-1 hover:bg-white/10 rounded transition">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-1">
                                        <button type="button" class="p-1 hover:bg-white/10 rounded transition"
                                            id="to-prev-month">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <button type="button" class="p-1 hover:bg-white/10 rounded transition"
                                            id="to-next-month">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-500 mb-2">
                                    <div>Ma</div>
                                    <div>Di</div>
                                    <div>Wo</div>
                                    <div>Do</div>
                                    <div>Vr</div>
                                    <div>Za</div>
                                    <div>Zo</div>
                                </div>
                                <div id="to-days" class="grid grid-cols-7 gap-1"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Cost Range Row -->
                <div class="flex gap-3 items-end">
                    <div class="flex-1 relative">
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Kosten bereik</label>
                        <div
                            class="flex gap-2 items-center bg-[#131928] border border-white/10 rounded-lg p-4 hover:border-white/20 transition">
                            <!-- Currency Selector -->
                            <div class="relative">
                                <button type="button" id="currency-btn"
                                    class="flex items-center gap-1 px-2 py-1.5 text-white text-sm font-semibold hover:bg-white/10 rounded transition">
                                    <span id="currency-symbol">€</span>
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </button>
                                <div id="currency-picker"
                                    class="hidden absolute top-full left-0 mt-2 bg-[#0f1421] border border-white/10 rounded-lg shadow-lg z-50 p-2 w-72">
                                    <div class="grid grid-cols-3 gap-1 max-h-80 overflow-y-auto">
                                        <button type="button" data-currency="EUR" data-symbol="€" data-rate="1.0"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">€
                                            EUR</button>
                                        <button type="button" data-currency="USD" data-symbol="$" data-rate="1.10"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">$
                                            USD</button>
                                        <button type="button" data-currency="GBP" data-symbol="£" data-rate="0.86"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">£
                                            GBP</button>
                                        <button type="button" data-currency="JPY" data-symbol="¥"
                                            data-rate="160.00"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">¥
                                            JPY</button>
                                        <button type="button" data-currency="CHF" data-symbol="Fr" data-rate="0.94"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">Fr
                                            CHF</button>
                                        <button type="button" data-currency="CAD" data-symbol="C$" data-rate="1.48"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">C$
                                            CAD</button>
                                        <button type="button" data-currency="AUD" data-symbol="A$" data-rate="1.66"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">A$
                                            AUD</button>
                                        <button type="button" data-currency="CNY" data-symbol="¥" data-rate="7.90"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">¥
                                            CNY</button>
                                        <button type="button" data-currency="INR" data-symbol="₹" data-rate="91.00"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">₹
                                            INR</button>
                                        <button type="button" data-currency="BRL" data-symbol="R$" data-rate="5.40"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">R$
                                            BRL</button>
                                        <button type="button" data-currency="MXN" data-symbol="$" data-rate="18.50"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">$
                                            MXN</button>
                                        <button type="button" data-currency="SEK" data-symbol="kr"
                                            data-rate="11.20"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">kr
                                            SEK</button>
                                        <button type="button" data-currency="NOK" data-symbol="kr"
                                            data-rate="11.60"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">kr
                                            NOK</button>
                                        <button type="button" data-currency="DKK" data-symbol="kr" data-rate="7.45"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">kr
                                            DKK</button>
                                        <button type="button" data-currency="SGD" data-symbol="S$" data-rate="1.48"
                                            class="currency-opt text-xs py-2 px-2 rounded hover:bg-blue-600/50 transition text-center">S$
                                            SGD</button>
                                    </div>
                                </div>
                            </div>

                            <div class="h-8 w-px bg-white/10"></div>

                            <div class="flex-1">
                                <div class="text-xs text-gray-500 mb-2">Min</div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400" id="min-currency-symbol">€</span>
                                    <input type="number" name="min_cost" placeholder="0" step="0.01"
                                        id="min_cost_input" value="{{ $minCost ?? '' }}"
                                        class="flex-1 bg-transparent text-white text-sm focus:outline-none placeholder-gray-600">
                                </div>
                            </div>
                            <div class="h-8 w-px bg-white/10"></div>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 mb-2">Max</div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400" id="max-currency-symbol">€</span>
                                    <input type="number" name="max_cost" placeholder="9999" step="0.01"
                                        id="max_cost_input" value="{{ $maxCost ?? '' }}"
                                        class="flex-1 bg-transparent text-white text-sm focus:outline-none placeholder-gray-600">
                                </div>
                            </div>
                        </div>
                        <div id="cost-range-display" class="text-xs text-gray-500 mt-1.5"></div>
                    </div>

                    <button type="submit"
                        class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg transition">
                        Filteren
                    </button>
                    @if ($search || $fromDate || $toDate || $minCost || $maxCost)
                        <a href="{{ request()->url() }}"
                            class="px-6 py-2.5 bg-gray-700/50 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition border border-gray-600/50">
                            Wissen
                        </a>
                    @endif
                </div>
            </form>

            <!-- Calendar JavaScript -->
            <style>
                @keyframes calendarFadeIn {
                    from {
                        opacity: 0;
                        transform: scale(0.98);
                    }

                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }

                .calendar-animating {
                    animation: calendarFadeIn 0.2s ease-out;
                }

                /* Hide number input spinners */
                input[type="number"]::-webkit-outer-spin-button,
                input[type="number"]::-webkit-inner-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                }

                input[type="number"] {
                    -moz-appearance: textfield;
                }
            </style>
            <script>
                class DatePicker {
                    constructor(buttonId, calendarId, displayId, inputId) {
                        this.btn = document.getElementById(buttonId);
                        this.calendar = document.getElementById(calendarId);
                        this.display = document.getElementById(displayId);
                        this.input = document.getElementById(inputId);
                        this.currentMonth = new Date().getMonth();
                        this.currentYear = new Date().getFullYear();

                        const prefix = buttonId.split('-')[0];
                        this.monthYearEl = this.calendar.querySelector('h3') || this.calendar.querySelector(
                            'span[id$="-month-year"]');
                        this.daysEl = this.calendar.querySelector(`[id$="-days"]`);
                        this.prevBtn = this.calendar.querySelector(`[id$="-prev-month"]`);
                        this.nextBtn = this.calendar.querySelector(`[id$="-next-month"]`);

                        this.monthYearBtn = this.calendar.querySelector(`[id$="-month-year-btn"]`);
                        this.monthYearPicker = this.calendar.querySelector(`[id$="-month-year-picker"]`);
                        this.yearDisplay = this.calendar.querySelector(`[id$="-year-display"]`);
                        this.yearPrevBtn = this.calendar.querySelector(`[id$="-year-prev"]`);
                        this.yearNextBtn = this.calendar.querySelector(`[id$="-year-next"]`);

                        this.init();
                    }

                    init() {
                        this.btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.toggle();
                        });

                        this.prevBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.prevMonth();
                        });

                        this.nextBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.nextMonth();
                        });

                        this.monthYearBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            this.toggleMonthYearPicker();
                        });

                        this.yearPrevBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.currentYear--;
                            this.updateYearDisplay();
                        });

                        this.yearNextBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.currentYear++;
                            this.updateYearDisplay();
                        });

                        const monthBtns = this.monthYearPicker.querySelectorAll('.month-btn');
                        monthBtns.forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                e.preventDefault();
                                this.currentMonth = parseInt(btn.dataset.month);
                                this.monthYearPicker.classList.add('hidden');
                                this.render();
                            });
                        });

                        document.addEventListener('click', (e) => {
                            if (!this.btn.contains(e.target) && !this.calendar.contains(e.target)) {
                                this.calendar.classList.add('hidden');
                                this.monthYearPicker.classList.add('hidden');
                            }
                        });

                        this.render();
                    }

                    toggleMonthYearPicker() {
                        this.monthYearPicker.classList.toggle('hidden');
                    }

                    updateYearDisplay() {
                        this.yearDisplay.textContent = this.currentYear;
                        this.render();
                    }

                    toggle() {
                        this.calendar.classList.toggle('hidden');
                        this.monthYearPicker.classList.add('hidden');
                    }

                    prevMonth() {
                        this.currentMonth--;
                        if (this.currentMonth < 0) {
                            this.currentMonth = 11;
                            this.currentYear--;
                        }
                        this.render();
                    }

                    nextMonth() {
                        this.currentMonth++;
                        if (this.currentMonth > 11) {
                            this.currentMonth = 0;
                            this.currentYear++;
                        }
                        this.render();
                    }

                    animateDays() {
                        this.daysEl.classList.remove('calendar-animating');
                        void this.daysEl.offsetWidth;
                        this.daysEl.classList.add('calendar-animating');
                    }

                    render() {
                        const monthNames = ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni',
                            'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December'
                        ];

                        this.monthYearEl.textContent = `${monthNames[this.currentMonth]} ${this.currentYear}`;
                        this.yearDisplay.textContent = this.currentYear;

                        const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
                        const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();

                        this.daysEl.innerHTML = '';

                        // Empty cells before month starts
                        for (let i = 0; i < (firstDay === 0 ? 6 : firstDay - 1); i++) {
                            this.daysEl.appendChild(document.createElement('div'));
                        }

                        // Days
                        for (let day = 1; day <= daysInMonth; day++) {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.textContent = day;
                            btn.className = 'p-2 text-xs text-white rounded hover:bg-blue-500/30 transition';

                            const dateStr =
                                `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

                            if (dateStr === this.input.value) {
                                btn.className =
                                    'p-2 text-xs text-white bg-blue-600 rounded hover:bg-blue-500 transition font-semibold';
                            }

                            btn.addEventListener('click', (e) => {
                                e.preventDefault();
                                this.selectDate(dateStr);
                            });

                            this.daysEl.appendChild(btn);
                        }

                        this.animateDays();
                    }

                    selectDate(dateStr) {
                        this.input.value = dateStr;
                        const date = new Date(dateStr);
                        const monthNames = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];
                        this.display.textContent = `${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
                        this.calendar.classList.add('hidden');
                        this.monthYearPicker.classList.add('hidden');
                    }
                }

                new DatePicker('from-date-btn', 'from-calendar', 'from-date-display', 'from_date_hidden');
                new DatePicker('to-date-btn', 'to-calendar', 'to-date-display', 'to_date_hidden');

                // Cost Range Filter
                const minInput = document.getElementById('min_cost_input');
                const maxInput = document.getElementById('max_cost_input');
                const costDisplay = document.getElementById('cost-range-display');

                function updateCostDisplay() {
                    const min = minInput.value;
                    const max = maxInput.value;
                    let display = '';

                    if (min && max) {
                        display = `€${parseFloat(min).toFixed(2)} – €${parseFloat(max).toFixed(2)}`;
                    } else if (min) {
                        display = `Vanaf €${parseFloat(min).toFixed(2)}`;
                    } else if (max) {
                        display = `Tot €${parseFloat(max).toFixed(2)}`;
                    }

                    costDisplay.textContent = display;
                }

                minInput.addEventListener('input', updateCostDisplay);
                maxInput.addEventListener('input', updateCostDisplay);

                // Validate min <= max
                minInput.addEventListener('change', () => {
                    if (minInput.value && maxInput.value) {
                        if (parseFloat(minInput.value) > parseFloat(maxInput.value)) {
                            minInput.value = maxInput.value;
                        }
                    }
                });

                maxInput.addEventListener('change', () => {
                    if (minInput.value && maxInput.value) {
                        if (parseFloat(maxInput.value) < parseFloat(minInput.value)) {
                            maxInput.value = minInput.value;
                        }
                    }
                });

                // Initialize display
                updateCostDisplay();

                // Currency Converter
                class CurrencyConverter {
                    constructor() {
                        // Load saved currency from localStorage, default to EUR
                        const savedCurrency = localStorage.getItem('selectedCurrency');

                        if (savedCurrency) {
                            const [currency, rate, symbol] = savedCurrency.split('|');
                            this.currentCurrency = currency;
                            this.currentRate = parseFloat(rate);
                            this.currentSymbol = symbol;
                            console.log('✓ Loaded saved currency from localStorage:', this.currentCurrency);
                        } else {
                            this.currentCurrency = 'EUR';
                            this.currentRate = 1.0;
                            this.currentSymbol = '€';
                            console.log('✓ No saved currency, using default EUR');
                        }

                        this.originalMinValue = minInput.value ? parseFloat(minInput.value) : null;
                        this.originalMaxValue = maxInput.value ? parseFloat(maxInput.value) : null;
                        this.init();
                        // NOTE: restoreUIState() is called from DOMContentLoaded to ensure all elements are loaded
                    }

                    init() {
                        const currencyBtn = document.getElementById('currency-btn');
                        const currencyPicker = document.getElementById('currency-picker');
                        const currencyOptions = document.querySelectorAll('.currency-opt');

                        currencyBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            currencyPicker.classList.toggle('hidden');
                        });

                        currencyOptions.forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                e.preventDefault();
                                this.currentCurrency = btn.dataset.currency;
                                this.currentRate = parseFloat(btn.dataset.rate);
                                this.currentSymbol = btn.dataset.symbol;
                                // Save to localStorage
                                localStorage.setItem('selectedCurrency',
                                    `${this.currentCurrency}|${this.currentRate}|${this.currentSymbol}`);
                                console.log('✓ Saved to localStorage:', this.currentCurrency);
                                this.updateUI();
                                currencyPicker.classList.add('hidden');
                            });
                        });

                        document.addEventListener('click', (e) => {
                            if (!currencyBtn.contains(e.target) && !currencyPicker.contains(e.target)) {
                                currencyPicker.classList.add('hidden');
                            }
                        });
                    }

                    restoreUIState() {
                        // Update UI to reflect loaded/saved currency without triggering data updates
                        document.getElementById('currency-symbol').textContent = this.currentSymbol;
                        document.getElementById('min-currency-symbol').textContent = this.currentSymbol;
                        document.getElementById('max-currency-symbol').textContent = this.currentSymbol;

                        // Update all currency symbols in tables and group headers
                        document.querySelectorAll('.currency-symbol-table, .group-symbol').forEach(el => {
                            el.textContent = this.currentSymbol;
                        });

                        // Convert and update input values
                        if (this.originalMinValue !== null) {
                            minInput.value = (this.originalMinValue * this.currentRate).toFixed(2);
                        }
                        if (this.originalMaxValue !== null) {
                            maxInput.value = (this.originalMaxValue * this.currentRate).toFixed(2);
                        }

                        // Update all cost displays in grouped records
                        document.querySelectorAll('[data-cost-original]').forEach(el => {
                            const originalCost = parseFloat(el.dataset.costOriginal);
                            const convertedCost = (originalCost * this.currentRate).toFixed(2);
                            el.textContent = convertedCost.replace('.', ',');
                        });

                        // Update cost range display text
                        const costDisplayEl = document.getElementById('cost-range-display');
                        const min = minInput.value;
                        const max = maxInput.value;
                        let display = '';

                        if (min && max) {
                            display =
                                `${this.currentSymbol}${parseFloat(min).toFixed(2).replace('.', ',')} – ${this.currentSymbol}${parseFloat(max).toFixed(2).replace('.', ',')}`;
                        } else if (min) {
                            display = `Vanaf ${this.currentSymbol}${parseFloat(min).toFixed(2).replace('.', ',')}`;
                        } else if (max) {
                            display = `Tot ${this.currentSymbol}${parseFloat(max).toFixed(2).replace('.', ',')}`;
                        }
                        costDisplayEl.textContent = display;
                    }

                    updateUI() {
                        console.log('🔄 records-grouped updateUI() CALLED with symbol:', this.currentSymbol, 'rate:', this
                            .currentRate);
                        // Update currency symbol in selector
                        document.getElementById('currency-symbol').textContent = this.currentSymbol;
                        document.getElementById('min-currency-symbol').textContent = this.currentSymbol;
                        document.getElementById('max-currency-symbol').textContent = this.currentSymbol;

                        // Update all currency symbols in tables and group headers
                        document.querySelectorAll('.currency-symbol-table, .group-symbol').forEach(el => {
                            el.textContent = this.currentSymbol;
                        });

                        // Convert and update input values
                        try {
                            if (this.originalMinValue !== null) {
                                minInput.value = (this.originalMinValue * this.currentRate).toFixed(2);
                            }
                            if (this.originalMaxValue !== null) {
                                minInput.value = (this.originalMaxValue * this.currentRate).toFixed(2);
                            }
                        } catch (e) {
                            console.error('❌ Error updating input values:', e);
                        }

                        // Update cost range display
                        try {
                            updateCostDisplay();
                        } catch (e) {
                            console.error('❌ Error in updateCostDisplay():', e);
                        }

                        // Update all cost displays in grouped records
                        console.log('About to update [data-cost-original] elements...');
                        const costElements = document.querySelectorAll('[data-cost-original]');
                        console.log('Found', costElements.length, 'elements with [data-cost-original]');
                        costElements.forEach((el, index) => {
                            const originalCost = parseFloat(el.dataset.costOriginal);
                            const convertedCost = (originalCost * this.currentRate).toFixed(2);
                            console.log('Element', index, '- Original EUR:', originalCost, '→ Converted:',
                                convertedCost, this.currentSymbol);
                            el.textContent = convertedCost.replace('.', ',');
                        });
                        console.log('✓ Updated all cost elements');

                        // Update cost range display text with current symbol
                        const costDisplayEl = document.getElementById('cost-range-display');
                        const min = minInput.value;
                        const max = maxInput.value;
                        let display = '';

                        if (min && max) {
                            display =
                                `${this.currentSymbol}${parseFloat(min).toFixed(2).replace('.', ',')} – ${this.currentSymbol}${parseFloat(max).toFixed(2).replace('.', ',')}`;
                        } else if (min) {
                            display = `Vanaf ${this.currentSymbol}${parseFloat(min).toFixed(2).replace('.', ',')}`;
                        } else if (max) {
                            display = `Tot ${this.currentSymbol}${parseFloat(max).toFixed(2).replace('.', ',')}`;
                        }
                        costDisplayEl.textContent = display;
                    }
                }

                const converter = new CurrencyConverter();

                // Listen for localStorage changes (from dashboard or other tabs)
                window.addEventListener('storage', function(event) {
                    if (event.key === 'selectedCurrency') {
                        console.log('📢 Storage event detected: selectedCurrency changed');
                        // Parse the saved currency
                        const [currency, rate, symbol] = event.newValue.split('|');
                        converter.currentCurrency = currency;
                        converter.currentRate = parseFloat(rate);
                        converter.currentSymbol = symbol;
                        console.log('✓ Updated converter from storage event:', currency, symbol);
                        // Update UI with new currency
                        converter.updateUI();
                    }
                });

                // Restore UI state when DOM is ready (so all cost elements are loaded)
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('DOMContentLoaded: Restoring currency UI state');
                    converter.restoreUIState();
                });

                // Listen to input changes and store original values
                minInput.addEventListener('input', () => {
                    if (converter.currentRate === 1.0) {
                        converter.originalMinValue = minInput.value ? parseFloat(minInput.value) : null;
                    }
                });

                maxInput.addEventListener('input', () => {
                    if (converter.currentRate === 1.0) {
                        converter.originalMaxValue = maxInput.value ? parseFloat(maxInput.value) : null;
                    }
                });
            </script>
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
                        <p class="text-sm font-semibold text-cyan-400 mono"><span class="group-symbol">€</span><span
                                data-cost-original="{{ $records->sum('costs') }}">{{ number_format($records->sum('costs'), 2, ',', '.') }}</span>
                        </p>
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
                                        <span class="currency-symbol-table">€</span><span
                                            data-cost-original="{{ $record->costs }}">{{ number_format($record->costs, 2, ',', '.') }}</span>
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
