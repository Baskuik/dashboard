<!DOCTYPE html>
<html lang="nl">

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

    {{-- GSAP Animation Library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

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
    </style>
</head>

<body class="bg-[#0b0f1a] text-gray-100 min-h-screen">

    {{-- Navigation --}}
    <x-navbar>
        <span class="text-white/20 text-sm">/</span>
        <span class="text-gray-400 text-sm">Dashboard</span>
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

            <form id="upload-form" method="POST" action="{{ route('upload.store') }}" enctype="multipart/form-data"
                class="space-y-4">
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

            <!-- Modal Backdrop -->
            <div id="confirm-modal-backdrop"
                class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-40 transition-opacity duration-200"></div>

            <!-- Confirmation Modal -->
            <div id="confirm-modal"
                class="hidden fixed inset-0 flex items-center justify-center z-50 transition-opacity duration-200">
                <div
                    class="bg-[#131928] border border-white/10 rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition">
                    <!-- Icon -->
                    <div class="flex justify-center mb-6">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4v2m0 6v2M7 5h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="text-lg font-semibold text-white text-center mb-3">Bestand vervangen?</h3>

                    <!-- Message -->
                    <p class="text-sm text-gray-400 text-center mb-8">
                        Weet je zeker dat je het huidige bestand wilt vervangen?<br>
                        <span class="text-xs text-gray-500 mt-2 block">De huidige gegevens zullen permanent worden
                            verwijderd.</span>
                    </p>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button type="button" id="modal-cancel"
                            class="flex-1 px-4 py-2.5 bg-gray-700/50 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition border border-gray-600/50">
                            Annuleren
                        </button>
                        <button type="button" id="modal-confirm"
                            class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg transition">
                            Ja, vervangen
                        </button>
                    </div>
                </div>
            </div>

            <script>
                const uploadForm = document.getElementById('upload-form');
                const confirmModal = document.getElementById('confirm-modal');
                const confirmBackdrop = document.getElementById('confirm-modal-backdrop');
                const modalCancel = document.getElementById('modal-cancel');
                const modalConfirm = document.getElementById('modal-confirm');
                const fileInput = document.getElementById('file');

                function showModal() {
                    confirmModal.classList.remove('hidden');
                    confirmBackdrop.classList.remove('hidden');
                }

                function hideModal() {
                    confirmModal.classList.add('hidden');
                    confirmBackdrop.classList.add('hidden');
                }

                uploadForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (!fileInput.files.length) return;

                    const hasExistingData = {{ $paginatedRecords->count() > 0 ? 'true' : 'false' }};

                    if (hasExistingData) {
                        showModal();
                    } else {
                        uploadForm.submit();
                    }
                });

                modalCancel.addEventListener('click', hideModal);
                confirmBackdrop.addEventListener('click', hideModal);

                modalConfirm.addEventListener('click', () => {
                    hideModal();
                    uploadForm.submit();
                });

                // ESC key to close
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !confirmModal.classList.contains('hidden')) {
                        hideModal();
                    }
                });

                // ** CRITICAL: Define chartInstances and storeChartInstance BEFORE charts.blade.php is included
                // This must be done here so that charts.blade.php's script tags can register the chart instances
                let chartInstances = {};
                window.storeChartInstance = function(name, instance) {
                    chartInstances[name] = instance;
                    console.log('✓ Stored chart instance:', name, 'Total instances:', Object.keys(chartInstances).length);
                };
            </script>
        </div>

        {{-- Show filters and stats always (whether widgets are selected or not) --}}
        <div class="border border-white/8 bg-[#131928] rounded-xl p-6 mb-8">
            <h2 class="text-base font-semibold text-white mb-4">Filter &amp; Zoeking</h2>
            <form method="GET" class="space-y-4" id="dashboard-filter-form">
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
                </div>

                <!-- Date Range Row -->
                <div class="flex gap-3 items-end">
                    <!-- From Date -->
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
                            <!-- From Calendar (simplified - same as records-grouped) -->
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

                    <!-- To Date -->
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
                            <!-- To Calendar -->
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
                                    <input type="number" name="max_cost" placeholder="0" step="0.01"
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
                        <a href="{{ route('dashboard') }}"
                            class="px-6 py-2.5 bg-gray-700/50 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition border border-gray-600/50">
                            Wissen
                        </a>
                    @endif
                </div>
            </form>
        </div>
        @include('dashboard.stats', ['stats' => $stats])

        {{-- Charts --}}
        @include('dashboard.charts', [
            'chartData' => $chartData,
            'kostenPerMaand' => $kostenPerMaand,
            'selectedWidgets' => $selectedWidgets,
        ])

        {{-- No Widgets Selected State --}}
        @if (empty($selectedWidgets))
            <div
                class="border border-white/8 bg-[#131928] rounded-xl p-16 mb-8 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-blue-500/10 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold text-white mb-2">Geen widgets geselecteerd</h2>
                <p class="text-gray-400 mb-8 max-w-md">Je hebt nog geen widgets geselecteerd voor je dashboard. Kies
                    widgets om je belangrijkste metriek en grafieken weer te geven.</p>
                <a href="{{ route('dashboard.select-widgets') }}"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition font-medium">
                    Widgets Beheren
                </a>
            </div>
        @endif


        {{-- Filter Scripts (Calendar & Currency) --}}
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
            // chartInstances already defined in the first script block (before charts.blade.php is included)
            // storeChartInstance already defined in the first script block

            // Copy DatePicker class from records-grouped (simplified for dashboard)
            class DatePicker {
                constructor(buttonId, calendarId, displayId, inputId) {
                    this.btn = document.getElementById(buttonId);
                    this.calendar = document.getElementById(calendarId);
                    this.display = document.getElementById(displayId);
                    this.input = document.getElementById(inputId);
                    this.currentMonth = new Date().getMonth();
                    this.currentYear = new Date().getFullYear();

                    this.monthYearEl = this.calendar.querySelector('[id$="-month-year"]');
                    this.daysEl = this.calendar.querySelector('[id$="-days"]');
                    this.prevBtn = this.calendar.querySelector('[id$="-prev-month"]');
                    this.nextBtn = this.calendar.querySelector('[id$="-next-month"]');

                    this.monthYearBtn = this.calendar.querySelector('[id$="-month-year-btn"]');
                    this.monthYearPicker = this.calendar.querySelector('[id$="-month-year-picker"]');
                    this.yearDisplay = this.calendar.querySelector('[id$="-year-display"]');
                    this.yearPrevBtn = this.calendar.querySelector('[id$="-year-prev"]');
                    this.yearNextBtn = this.calendar.querySelector('[id$="-year-next"]');

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
                    const monthNames = ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus',
                        'September', 'Oktober', 'November', 'December'
                    ];
                    this.monthYearEl.textContent = `${monthNames[this.currentMonth]} ${this.currentYear}`;
                    this.yearDisplay.textContent = this.currentYear;

                    const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
                    const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                    this.daysEl.innerHTML = '';

                    for (let i = 0; i < (firstDay === 0 ? 6 : firstDay - 1); i++) {
                        this.daysEl.appendChild(document.createElement('div'));
                    }

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

                    // Sync filters immediately so stat card clicks have current value
                    if (typeof syncCurrentFilters !== 'undefined') {
                        syncCurrentFilters();
                    }

                    // Trigger dashboard data update when date is selected
                    if (typeof updateDashboardData !== 'undefined') {
                        updateDashboardData();
                    }
                }
            }

            new DatePicker('from-date-btn', 'from-calendar', 'from-date-display', 'from_date_hidden');
            new DatePicker('to-date-btn', 'to-calendar', 'to-date-display', 'to_date_hidden');

            // Currency & Cost Filter
            const minInput = document.getElementById('min_cost_input');
            const maxInput = document.getElementById('max_cost_input');
            const costDisplay = document.getElementById('cost-range-display');

            // Initialize stat card handlers (click handlers for stat cards)
            function initStatCardHandlers() {
                // This function initializes any event handlers on stat cards
                // Currently a placeholder but can be expanded for stat card interactions
            }

            function updateCostDisplay() {
                const min = minInput.value;
                const max = maxInput.value;
                const symbol = window.currencyConverter ? window.currencyConverter.currentSymbol : '€';
                let display = '';
                if (min && max) {
                    display = `${symbol}${parseFloat(min).toFixed(2)} – ${symbol}${parseFloat(max).toFixed(2)}`;
                } else if (min) {
                    display = `Vanaf ${symbol}${parseFloat(min).toFixed(2)}`;
                } else if (max) {
                    display = `Tot ${symbol}${parseFloat(max).toFixed(2)}`;
                }
                costDisplay.textContent = display;
            }

            let costTimeout;
            minInput.addEventListener('input', function() {
                clearTimeout(costTimeout);
                updateCostDisplay();
                // Sync filters immediately so stat card clicks have current value
                syncCurrentFilters();
                costTimeout = setTimeout(() => {
                    if (typeof updateDashboardData !== 'undefined') {
                        updateDashboardData();
                    }
                }, 500);
            });
            maxInput.addEventListener('input', function() {
                clearTimeout(costTimeout);
                updateCostDisplay();
                // Sync filters immediately so stat card clicks have current value
                syncCurrentFilters();
                costTimeout = setTimeout(() => {
                    if (typeof updateDashboardData !== 'undefined') {
                        updateDashboardData();
                    }
                }, 500);
            });
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

                    // Update originalMinValue and originalMaxValue whenever the user edits the inputs
                    minInput.addEventListener('input', () => {
                        this.originalMinValue =
                            minInput.value === '' ? null : parseFloat(minInput.value) / this.currentRate;
                    });

                    maxInput.addEventListener('input', () => {
                        this.originalMaxValue =
                            maxInput.value === '' ? null : parseFloat(maxInput.value) / this.currentRate;
                    });

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
                    // Update UI to reflect loaded/saved currency without triggering dashboard update
                    console.log('restoreUIState() called, symbol:', this.currentSymbol);

                    try {
                        document.getElementById('currency-symbol').textContent = this.currentSymbol;
                        console.log('✓ Updated currency-symbol');
                    } catch (e) {
                        console.error('Failed to update currency-symbol:', e);
                    }

                    try {
                        document.getElementById('min-currency-symbol').textContent = this.currentSymbol;
                        console.log('✓ Updated min-currency-symbol');
                    } catch (e) {
                        console.error('Failed to update min-currency-symbol:', e);
                    }

                    try {
                        document.getElementById('max-currency-symbol').textContent = this.currentSymbol;
                        console.log('✓ Updated max-currency-symbol');
                    } catch (e) {
                        console.error('Failed to update max-currency-symbol:', e);
                    }

                    if (this.originalMinValue !== null) {
                        minInput.value = (this.originalMinValue * this.currentRate).toFixed(2);
                    }
                    if (this.originalMaxValue !== null) {
                        maxInput.value = (this.originalMaxValue * this.currentRate).toFixed(2);
                    }
                }

                updateUI() {
                    console.log('🔄 updateUI() CALLED - about to update symbols');
                    console.log('CurrencyConverter.updateUI() called, currency:', this.currentCurrency, 'rate:', this
                        .currentRate, 'symbol:', this.currentSymbol);

                    try {
                        const currencySymbol = document.getElementById('currency-symbol');
                        if (currencySymbol) {
                            currencySymbol.textContent = this.currentSymbol;
                            console.log('✓ Updated currency-symbol to:', this.currentSymbol);
                        } else {
                            console.error('❌ currency-symbol element not found');
                        }
                    } catch (e) {
                        console.error('Error updating currency-symbol:', e);
                    }

                    try {
                        const minSymbol = document.getElementById('min-currency-symbol');
                        if (minSymbol) {
                            minSymbol.textContent = this.currentSymbol;
                            console.log('✓ Updated min-currency-symbol to:', this.currentSymbol);
                        } else {
                            console.error('❌ min-currency-symbol element not found');
                        }
                    } catch (e) {
                        console.error('Error updating min-currency-symbol:', e);
                    }

                    try {
                        const maxSymbol = document.getElementById('max-currency-symbol');
                        if (maxSymbol) {
                            maxSymbol.textContent = this.currentSymbol;
                            console.log('✓ Updated max-currency-symbol to:', this.currentSymbol);
                        } else {
                            console.error('❌ max-currency-symbol element not found');
                        }
                    } catch (e) {
                        console.error('Error updating max-currency-symbol:', e);
                    }

                    if (this.originalMinValue !== null) {
                        minInput.value = (this.originalMinValue * this.currentRate).toFixed(2);
                    }
                    if (this.originalMaxValue !== null) {
                        maxInput.value = (this.originalMaxValue * this.currentRate).toFixed(2);
                    }

                    updateCostDisplay();

                    const min = minInput.value;
                    const max = maxInput.value;
                    let display = '';
                    if (min && max) {
                        display =
                            `${this.currentSymbol}${parseFloat(min).toFixed(2)} – ${this.currentSymbol}${parseFloat(max).toFixed(2)}`;
                    } else if (min) {
                        display = `Vanaf ${this.currentSymbol}${parseFloat(min).toFixed(2)}`;
                    } else if (max) {
                        display = `Tot ${this.currentSymbol}${parseFloat(max).toFixed(2)}`;
                    }
                    costDisplay.textContent = display;

                    // Sync filters immediately so stat card clicks have current value
                    if (typeof syncCurrentFilters !== 'undefined') {
                        syncCurrentFilters();
                    }

                    // Trigger dashboard data update when currency changes
                    if (typeof updateDashboardData !== 'undefined') {
                        console.log('Calling updateDashboardData from currency change');
                        updateDashboardData();
                    }
                }
            }

            window.currencyConverter = new CurrencyConverter();
        </script>

        {{-- Real-time Dashboard Filtering --}}
        <script>
            // chartInstances already defined at top of script
            // storeChartInstance already defined at top of script

            let currentFilters = {
                search: '{{ $search ?? '' }}',
                from_date: '{{ $fromDate ?? '' }}',
                to_date: '{{ $toDate ?? '' }}',
                min_cost: '{{ $minCost ?? '' }}',
                max_cost: '{{ $maxCost ?? '' }}',
                currency: 'EUR',
                currency_rate: 1.0
            };

            // Update currentFilters with saved currency from CurrencyConverter
            currentFilters.currency = window.currencyConverter.currentCurrency;
            currentFilters.currency_rate = window.currencyConverter.currentRate;
            console.log('✓ Updated currentFilters with saved currency:', currentFilters.currency, 'rate:', currentFilters
                .currency_rate);

            // Initialize dashboard on page load
            document.addEventListener('DOMContentLoaded', function() {
                // Check if saved currency is different from EUR
                const hasSavedCurrency = window.currencyConverter.currentCurrency !== 'EUR';

                // If there are any active filters, update the dashboard data
                const hasActiveFilters = currentFilters.search || currentFilters.from_date || currentFilters
                    .to_date ||
                    currentFilters.min_cost || currentFilters.max_cost;

                // First: Update UI with correct currency symbols (with small delay to ensure DOM is ready)
                if (hasSavedCurrency) {
                    console.log('Restoring UI state with saved currency:', window.currencyConverter.currentCurrency);
                    setTimeout(() => {
                        window.currencyConverter.restoreUIState();
                    }, 50);
                }

                // Then: Update dashboard data if there are active filters OR if a non-EUR currency is saved
                if (hasActiveFilters || hasSavedCurrency) {
                    console.log('Triggering updateDashboardData - hasSavedCurrency:', hasSavedCurrency,
                        'hasActiveFilters:', hasActiveFilters);
                    // Small delay to ensure DOM is ready
                    setTimeout(() => updateDashboardData(), 100);
                }

                // Initialize stat card handlers
                initStatCardHandlers();
            });

            // Handle filter form submission via AJAX
            document.getElementById('dashboard-filter-form').addEventListener('submit', function(e) {
                e.preventDefault();
                updateDashboardData();
            });

            // Update on input changes (debounced for search)
            const searchInput = document.querySelector('input[name="search"]');
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                // Sync filters immediately so stat card clicks have current value
                syncCurrentFilters();
                searchTimeout = setTimeout(() => updateDashboardData(), 500);
            });

            // Sync filters when cost inputs change
            minInput.addEventListener('change', function() {
                syncCurrentFilters();
            });
            maxInput.addEventListener('change', function() {
                syncCurrentFilters();
            });

            // Helper to get currency info from the converter
            function getCurrencyInfo() {
                // Use the global CurrencyConverter instance which has the correct current values
                if (window.currencyConverter) {
                    return {
                        symbol: window.currencyConverter.currentSymbol,
                        currency: window.currencyConverter.currentCurrency,
                        rate: window.currencyConverter.currentRate
                    };
                }
                // Fallback if CurrencyConverter not initialized
                return {
                    symbol: '€',
                    currency: 'EUR',
                    rate: 1.0
                };
            }

            // Update currentFilters from form inputs - this keeps it in sync with what user types
            function syncCurrentFilters() {
                const form = document.getElementById('dashboard-filter-form');
                if (!form) return;

                const currencyInfo = getCurrencyInfo();

                const searchInput = form.querySelector('input[name="search"]');
                const fromDateInput = document.getElementById('from_date_hidden');
                const toDateInput = document.getElementById('to_date_hidden');
                const minCostInput = document.getElementById('min_cost_input');
                const maxCostInput = document.getElementById('max_cost_input');

                currentFilters = {
                    search: searchInput ? searchInput.value : '',
                    from_date: fromDateInput ? fromDateInput.value : '',
                    to_date: toDateInput ? toDateInput.value : '',
                    min_cost: minCostInput ? minCostInput.value : '',
                    max_cost: maxCostInput ? maxCostInput.value : '',
                    currency: currencyInfo.currency,
                    currency_rate: currencyInfo.rate
                };
            }

            // Main function to fetch and update dashboard data
            let dashboardRequestController = null;
            let dashboardRequestSeq = 0;

            function updateDashboardData() {
                const requestSeq = ++dashboardRequestSeq;
                dashboardRequestController?.abort();
                dashboardRequestController = new AbortController();

                const currencyInfo = getCurrencyInfo();

                // First sync the current filters (including currency)
                syncCurrentFilters();

                // Build query parameters from currentFilters
                const queryParams = new URLSearchParams();
                for (const [key, value] of Object.entries(currentFilters)) {
                    queryParams.append(key, value);
                }

                // Fetch data from API
                fetch('{{ route('api.dashboard-data') }}?' + queryParams.toString(), {
                        method: 'GET',
                        signal: dashboardRequestController.signal,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Ignore stale responses
                        if (requestSeq !== dashboardRequestSeq) {
                            return;
                        }

                        updateStatCards(data.stats, currencyInfo);
                        updateCharts(data.charts, data.kostenPerMaand, currencyInfo);
                        updateCostDisplay();
                        // Re-initialize stat card handlers after updating
                        initStatCardHandlers();
                    })
                    .catch(error => {
                        if (error.name !== 'AbortError') {
                            console.error('Error fetching dashboard data:', error);
                        }
                    });
            }

            // Update stat cards with new values
            function updateStatCards(stats, currencyInfo) {
                // Update Totaal acties
                const totalActionsCard = document.querySelector('[href="{{ route('records.by-action') }}"]');
                if (totalActionsCard) {
                    totalActionsCard.querySelector('.mono').textContent = stats.total_actions;
                }

                // Update Totale kosten - convert EUR to current currency
                const costCard = document.querySelector('[href="{{ route('records.by-cost') }}"]');
                if (costCard) {
                    const convertedCost = parseFloat(stats.total_cost) * currencyInfo.rate;
                    costCard.querySelector('.mono').textContent =
                        `${currencyInfo.symbol}\u00A0${convertedCost.toLocaleString('nl-NL', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
                }

                // Update Gem. duur
                const durationCard = document.querySelector('[href="{{ route('records.by-duration') }}"]');
                if (durationCard) {
                    durationCard.querySelector('.mono').textContent =
                        `${parseFloat(stats.avg_duration).toLocaleString('nl-NL')}u`;
                }

                // Update Medewerkers
                const employeeCard = document.querySelector('[href="{{ route('records.by-employee') }}"]');
                if (employeeCard) {
                    employeeCard.querySelector('.mono').textContent = stats.total_employees;
                }
            }

            // Update charts with new data
            function updateCharts(chartsData, kostenPerMaandData, currencyInfo) {
                console.log('updateCharts called with rate:', currencyInfo.rate);

                // Use ORIGINAL EUR data for conversion, not the data passed in
                // This prevents double-conversion when switching currencies
                const originalChartData = window.originalChartData || chartsData;
                const originalKostenPerMaandData = window.originalKostenPerMaandData || kostenPerMaandData;

                // Update Actions Per Month chart (non-currency, no conversion needed)
                if (window.actionsPerMonthChart && chartInstances.actionsPerMonth) {
                    const labels = Object.keys(originalChartData.actionsPerMonth || {});
                    const data = Object.values(originalChartData.actionsPerMonth || {});
                    chartInstances.actionsPerMonth.data.labels = labels;
                    chartInstances.actionsPerMonth.data.datasets[0].data = data;
                    chartInstances.actionsPerMonth.update();
                    console.log('✓ actionsPerMonth updated');
                }

                // Update Cost Per Employee chart - convert from ORIGINAL EUR data
                if (window.costPerEmployeeChart && chartInstances.costPerEmployee) {
                    const originalData = Object.values(originalChartData.costPerEmployee || {});
                    const convertedData = originalData.map(v => Math.round(v * currencyInfo.rate * 100) / 100);
                    chartInstances.costPerEmployee.data.labels = Object.keys(originalChartData.costPerEmployee || {});
                    chartInstances.costPerEmployee.data.datasets[0].data = convertedData;
                    chartInstances.costPerEmployee.data.datasets[0].label = `Kosten (${currencyInfo.symbol})`;
                    chartInstances.costPerEmployee.update();
                    console.log('✓ costPerEmployee updated with rate', currencyInfo.rate, 'converted data:', convertedData);
                }

                // Update Actions By Type chart (non-currency, no conversion needed)
                if (window.actionsByTypeChart && chartInstances.actionsByType) {
                    const labels = Object.keys(originalChartData.actionsByType || {});
                    const data = Object.values(originalChartData.actionsByType || {});
                    chartInstances.actionsByType.data.labels = labels;
                    chartInstances.actionsByType.data.datasets[0].data = data;
                    chartInstances.actionsByType.update();
                    console.log('✓ actionsByType updated');
                }

                // Update Kosten Per Maand chart - convert from ORIGINAL EUR data
                if (window.kostenPerMaandChart && chartInstances.kostenPerMaand) {
                    const originalData = Object.values(originalKostenPerMaandData || {});
                    console.log('Original kostenPerMaand EUR data:', originalData);
                    const convertedData = originalData.map(v => Math.round(v * currencyInfo.rate * 100) / 100);
                    console.log('Converted kostenPerMaand data (×' + currencyInfo.rate + '):', convertedData);

                    chartInstances.kostenPerMaand.data.labels = Object.keys(originalKostenPerMaandData || {});
                    chartInstances.kostenPerMaand.data.datasets[0].data = convertedData;
                    chartInstances.kostenPerMaand.data.datasets[0].label = `Kosten (${currencyInfo.symbol})`;
                    chartInstances.kostenPerMaand.update();
                    console.log('✓ kostenPerMaand chart updated');
                } else {
                    console.warn('⚠️ kostenPerMaand chart not ready');
                }

                // Update chart container titles (run always, outside chart checks)
                document.querySelectorAll('.border.border-white\\/8 h3').forEach(title => {
                    if (title.textContent.includes('Kosten per maand')) {
                        title.textContent = `Kosten per maand (${currencyInfo.symbol})`;
                    } else if (title.textContent.includes('Kosten per medewerker')) {
                        title.textContent = `Kosten per medewerker (${currencyInfo.symbol})`;
                    }
                });

            }

            // User menu click handler handled by navbar component
        </script>

    </main>

    <script>
        // GSAP Animations - Dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat cards - fade in + slide up
            gsap.to('[class*="stat-card"]', {
                duration: 0.8,
                opacity: 1,
                y: 0,
                stagger: 0.15,
                ease: 'power2.out',
            });

            // Initial state for stat cards
            gsap.set('[class*="stat-card"]', {
                opacity: 0,
                y: 30
            });

            // Animate charts - fade in + scale
            setTimeout(() => {
                gsap.to('[class*="chart-container"]', {
                    duration: 0.8,
                    opacity: 1,
                    scale: 1,
                    stagger: 0.2,
                    ease: 'back.out',
                });
                gsap.set('[class*="chart-container"]', {
                    opacity: 0,
                    scale: 0.95
                });
            }, 300);

            // Animate table rows - staggered fade in
            setTimeout(() => {
                const tableRows = document.querySelectorAll('tbody tr');
                gsap.to(tableRows, {
                    duration: 0.5,
                    opacity: 1,
                    x: 0,
                    stagger: 0.1,
                    ease: 'power1.out',
                });
                gsap.set(tableRows, {
                    opacity: 0,
                    x: -20
                });
            }, 600);

            // Hover animation for stat cards
            document.querySelectorAll('[class*="stat-card"]').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    gsap.to(this, {
                        duration: 0.3,
                        scale: 1.03,
                        boxShadow: '0 10px 30px rgba(59, 130, 246, 0.2)',
                        ease: 'power2.out',
                    });
                });
                card.addEventListener('mouseleave', function() {
                    gsap.to(this, {
                        duration: 0.3,
                        scale: 1,
                        boxShadow: 'none',
                        ease: 'power2.out',
                    });
                });
            });

            // Hover animation for widget cards
            document.querySelectorAll('[class*="widget"]').forEach(widget => {
                if (widget.classList.contains('widget-card')) {
                    widget.addEventListener('mouseenter', function() {
                        gsap.to(this, {
                            duration: 0.3,
                            borderColor: 'rgba(59, 130, 246, 0.5)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            ease: 'power2.out',
                        });
                    });
                    widget.addEventListener('mouseleave', function() {
                        gsap.to(this, {
                            duration: 0.3,
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            backgroundColor: 'transparent',
                            ease: 'power2.out',
                        });
                    });
                }
            });

            // Animate buttons on hover
            document.querySelectorAll('button, a[class*="btn"]').forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    gsap.to(this, {
                        duration: 0.2,
                        scale: 1.05,
                        ease: 'power2.out',
                    });
                });
                btn.addEventListener('mouseleave', function() {
                    gsap.to(this, {
                        duration: 0.2,
                        scale: 1,
                        ease: 'power2.out',
                    });
                });
            });

            // Animate number counters (for stats)
            function animateValue(element, start, end, duration) {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.textContent = value.toLocaleString('nl-NL');
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            }

            // Apply counter animation to stat numbers
            document.querySelectorAll('[class*="stat-card"] .mono').forEach(element => {
                const text = element.textContent.trim();
                const number = parseInt(text.replace(/\D/g, ''), 10);
                if (!isNaN(number) && number > 0) {
                    animateValue(element, 0, number, 1000);
                }
            });
        });
    </script>
</body>

</html>
