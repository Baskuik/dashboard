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

        {{-- Stats --}}
        @include('dashboard.stats', ['stats' => $stats])

        {{-- Charts --}}
        @include('dashboard.charts', ['chartData' => $chartData, 'kostenPerMaand' => $kostenPerMaand])

    </main>
</body>

</html>
