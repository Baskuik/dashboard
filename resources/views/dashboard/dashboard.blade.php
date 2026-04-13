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
                    <span class="text-gray-400 text-sm">Dashboard</span>
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
            </script>
        </div>

        {{-- Filters Section --}}
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
                            <input type="hidden" name="from_date" id="from_date_hidden"
                                value="{{ $fromDate ?? '' }}">
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
                        <a href="{{ route('dashboard') }}"
                            class="px-6 py-2.5 bg-gray-700/50 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition border border-gray-600/50">
                            Wissen
                        </a>
                    @endif
                </div>
            </form>
        </div>

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
                    this.currentCurrency = 'EUR';
                    this.currentRate = 1.0;
                    this.currentSymbol = '€';
                    this.originalMinValue = minInput.value ? parseFloat(minInput.value) : null;
                    this.originalMaxValue = maxInput.value ? parseFloat(maxInput.value) : null;
                    this.init();
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

                updateUI() {
                    document.getElementById('currency-symbol').textContent = this.currentSymbol;
                    document.getElementById('min-currency-symbol').textContent = this.currentSymbol;
                    document.getElementById('max-currency-symbol').textContent = this.currentSymbol;

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
                        updateDashboardData();
                    }
                }
            }

            window.currencyConverter = new CurrencyConverter();
        </script>

        {{-- Real-time Dashboard Filtering --}}
        <script>
            let chartInstances = {
                actionsPerMonth: null,
                costPerEmployee: null,
                actionsByType: null,
                kostenPerMaand: null
            };

            let currentFilters = {
                search: '{{ $search ?? '' }}',
                from_date: '{{ $fromDate ?? '' }}',
                to_date: '{{ $toDate ?? '' }}',
                min_cost: '{{ $minCost ?? '' }}',
                max_cost: '{{ $maxCost ?? '' }}',
                currency: 'EUR',
                currency_rate: 1.0
            };

            // Initialize dashboard on page load
            document.addEventListener('DOMContentLoaded', function() {
                // If there are any active filters, update the dashboard data
                const hasActiveFilters = currentFilters.search || currentFilters.from_date || currentFilters
                    .to_date ||
                    currentFilters.min_cost || currentFilters.max_cost;
                if (hasActiveFilters) {
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
                const currencyInfo = getCurrencyInfo();

                currentFilters = {
                    search: form.querySelector('input[name="search"]').value,
                    from_date: document.getElementById('from_date_hidden').value,
                    to_date: document.getElementById('to_date_hidden').value,
                    min_cost: document.getElementById('min_cost_input').value,
                    max_cost: document.getElementById('max_cost_input').value,
                    currency: currencyInfo.currency,
                    currency_rate: currencyInfo.rate
                };
            }

            // Main function to fetch and update dashboard data
            function updateDashboardData() {
                const form = document.getElementById('dashboard-filter-form');
                const formData = new FormData(form);

                const currencyInfo = getCurrencyInfo();
                formData.append('currency', currencyInfo.currency);
                formData.append('currency_rate', currencyInfo.rate);

                // Update current filters
                syncCurrentFilters();

                // Fetch data from API
                fetch('{{ route('api.dashboard-data') }}?' + new URLSearchParams(Object.entries(currentFilters)), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        updateStatCards(data.stats, currencyInfo);
                        updateCharts(data.charts, data.kostenPerMaand, currencyInfo);
                        updateCostDisplay(data.kostenPerMaand, currencyInfo);
                        // Re-initialize stat card handlers after updating
                        initStatCardHandlers();
                    })
                    .catch(error => console.error('Error fetching dashboard data:', error));
            }

            // Update stat cards with new values
            function updateStatCards(stats, currencyInfo) {
                const symbols = {
                    'total_actions': 'actions',
                    'total_cost': 'currency',
                    'avg_duration': 'duration',
                    'total_employees': 'employees'
                };

                // Update Totaal acties
                const totalActionsCard = document.querySelector('[href="{{ route('records.by-action') }}"]');
                if (totalActionsCard) {
                    totalActionsCard.querySelector('.mono').textContent = stats.total_actions;
                }

                // Update Totale kosten
                const costCard = document.querySelector('[href="{{ route('records.by-cost') }}"]');
                if (costCard) {
                    costCard.querySelector('.mono').textContent =
                        `${currencyInfo.symbol} ${parseFloat(stats.total_cost).toLocaleString('nl-NL', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
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
                // Update Actions Per Month chart
                if (window.actionsPerMonthChart && chartInstances.actionsPerMonth) {
                    chartInstances.actionsPerMonth.data.labels = Object.keys(chartsData.actionsPerMonth);
                    chartInstances.actionsPerMonth.data.datasets[0].data = Object.values(chartsData.actionsPerMonth);
                    chartInstances.actionsPerMonth.update('none');
                }

                // Update Cost Per Employee chart
                if (window.costPerEmployeeChart && chartInstances.costPerEmployee) {
                    chartInstances.costPerEmployee.data.labels = Object.keys(chartsData.costPerEmployee);
                    chartInstances.costPerEmployee.data.datasets[0].data = Object.values(chartsData.costPerEmployee)
                        .map(v =>
                            Math.round(v * 100) / 100
                        );
                    chartInstances.costPerEmployee.data.datasets[0].label = `Kosten (${currencyInfo.symbol})`;
                    chartInstances.costPerEmployee.update('none');
                }

                // Update Actions By Type chart
                if (window.actionsByTypeChart && chartInstances.actionsByType) {
                    chartInstances.actionsByType.data.labels = Object.keys(chartsData.actionsByType);
                    chartInstances.actionsByType.data.datasets[0].data = Object.values(chartsData.actionsByType);
                    chartInstances.actionsByType.update('none');
                }

                // Update Kosten Per Maand chart
                if (window.kostenPerMaandChart && chartInstances.kostenPerMaand) {
                    chartInstances.kostenPerMaand.data.labels = Object.keys(kostenPerMaandData);
                    chartInstances.kostenPerMaand.data.datasets[0].data = Object.values(kostenPerMaandData).map(
                        v => Math.round(v * 100) / 100
                    );
                    chartInstances.kostenPerMaand.data.datasets[0].label = `Kosten (${currencyInfo.symbol})`;
                    chartInstances.kostenPerMaand.update('none');
                }

                // Update chart container titles
                document.querySelectorAll('.border.border-white\\/8 h3').forEach(title => {
                    if (title.textContent.includes('Kosten per maand')) {
                        title.textContent = `Kosten per maand (${currencyInfo.symbol})`;
                    } else if (title.textContent.includes('Kosten per medewerker')) {
                        title.textContent = `Kosten per medewerker (${currencyInfo.symbol})`;
                    }
                });
            }

            // Store chart instances when they're created
            window.storeChartInstance = function(name, instance) {
                chartInstances[name] = instance;
            };

            // Initialize stat card click handlers - call this once on page load
            function initStatCardHandlers() {
                document.querySelectorAll('.stat-card').forEach(card => {
                    card.style.cursor = 'pointer';
                    card.removeEventListener('click', handleStatCardClick); // Remove old listener if exists
                    card.addEventListener('click', handleStatCardClick);
                });
            }

            // Handle stat card clicks with current filters
            function handleStatCardClick(e) {
                e.preventDefault();
                e.stopPropagation();

                const href = this.getAttribute('href');
                if (href) {
                    // Make sure filters are synced
                    syncCurrentFilters();

                    // Build query string with only non-empty filters
                    const params = new URLSearchParams();
                    if (currentFilters.search) params.append('search', currentFilters.search);
                    if (currentFilters.from_date) params.append('from_date', currentFilters.from_date);
                    if (currentFilters.to_date) params.append('to_date', currentFilters.to_date);
                    if (currentFilters.min_cost) params.append('min_cost', currentFilters.min_cost);
                    if (currentFilters.max_cost) params.append('max_cost', currentFilters.max_cost);

                    const queryString = params.toString();
                    window.location.href = href + (queryString ? '?' + queryString : '');
                }
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                initStatCardHandlers();
            });
        </script>

        @include('dashboard.stats', ['stats' => $stats])

        {{-- Charts --}}
        @include('dashboard.charts', ['chartData' => $chartData, 'kostenPerMaand' => $kostenPerMaand])

    </main>
</body>

</html>
