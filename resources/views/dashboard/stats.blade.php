{{--
    Stat cards – each links to a filtered overview page.
    Routes expected:
      records.by-employee   – sorted/grouped by medewerker
      records.by-action     – sorted/grouped by actie
      records.by-cost       – sorted by kosten desc
      records.by-duration   – sorted by uren desc
--}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    {{-- Totaal acties --}}
    <a href="{{ route('records.by-action') }}?{{ http_build_query(request()->query()) }}"
        class="stat-card group border border-gray-200 dark:border-white/8 bg-white dark:bg-[#131928] rounded-xl p-5 flex flex-col gap-3 hover:border-blue-400 dark:hover:border-blue-500/30 transition shadow-sm dark:shadow-none">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-500 uppercase tracking-wider">Totaal
                acties</span>
            <div
                class="w-8 h-8 bg-blue-500/15 rounded-lg flex items-center justify-center group-hover:bg-blue-500/25 transition">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
        <div>
            <p class="text-3xl font-semibold text-gray-900 dark:text-white mono">{{ $stats['total_actions'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-600 mt-1">records geregistreerd</p>
        </div>
        <span class="text-xs text-blue-400 group-hover:text-blue-300 transition flex items-center gap-1">
            Bekijk per actie
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </span>
    </a>

    {{-- Totale kosten --}}
    <a href="{{ route('records.by-cost') }}?{{ http_build_query(request()->query()) }}"
        class="stat-card group border border-gray-200 dark:border-white/8 bg-white dark:bg-[#131928] rounded-xl p-5 flex flex-col gap-3 hover:border-cyan-400 dark:hover:border-cyan-500/30 transition shadow-sm dark:shadow-none">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-500 uppercase tracking-wider">Totale
                kosten</span>
            <div
                class="w-8 h-8 bg-cyan-500/15 rounded-lg flex items-center justify-center group-hover:bg-cyan-500/25 transition">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div>
            <p id="stat-total-cost" class="text-3xl font-semibold text-gray-900 dark:text-white mono"
                data-value="{{ $stats['total_cost'] ?? 0 }}">
                <span class="currency-symbol">€</span>&nbsp;<span
                    class="currency-value">{{ number_format($stats['total_cost'] ?? 0, 0, ',', '.') }}</span>
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-600 mt-1">totale uitgaven</p>
        </div>
        <span class="text-xs text-cyan-400 group-hover:text-cyan-300 transition flex items-center gap-1">
            Bekijk per kosten
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </span>
    </a>

    {{-- Gemiddelde duur --}}
    <a href="{{ route('records.by-duration') }}?{{ http_build_query(request()->query()) }}"
        class="stat-card group border border-gray-200 dark:border-white/8 bg-white dark:bg-[#131928] rounded-xl p-5 flex flex-col gap-3 hover:border-emerald-400 dark:hover:border-emerald-500/30 transition shadow-sm dark:shadow-none">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-700 dark:text-gray-500 uppercase tracking-wider">Gem. duur</span>
            <div
                class="w-8 h-8 bg-emerald-500/15 rounded-lg flex items-center justify-center group-hover:bg-emerald-500/25 transition">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div>
            <p class="text-3xl font-semibold text-gray-900 dark:text-white mono">
                {{ number_format($stats['avg_duration'] ?? 0, 1, ',', '.') }}u</p>
            <p class="text-xs text-gray-600 dark:text-gray-600 mt-1">gemiddeld per actie</p>
        </div>
        <span
            class="text-xs text-emerald-600 dark:text-emerald-400 group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition flex items-center gap-1">
            Bekijk per duur
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </span>
    </a>

    {{-- Medewerkers --}}
    <a href="{{ route('records.by-employee') }}?{{ http_build_query(request()->query()) }}"
        class="stat-card group border border-gray-200 dark:border-white/8 bg-white dark:bg-[#131928] rounded-xl p-5 flex flex-col gap-3 hover:border-violet-400 dark:hover:border-violet-500/30 transition shadow-sm dark:shadow-none">
        <div class="flex items-center justify-between">
            <span
                class="text-xs font-medium text-gray-600 dark:text-gray-500 uppercase tracking-wider">Medewerkers</span>
            <div
                class="w-8 h-8 bg-violet-500/15 rounded-lg flex items-center justify-center group-hover:bg-violet-500/25 transition">
                <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>
        <div>
            <p class="text-3xl font-semibold text-gray-900 dark:text-white mono">{{ $stats['total_employees'] ?? 0 }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-600 mt-1">betrokken medewerkers</p>
        </div>
        <span class="text-xs text-violet-400 group-hover:text-violet-300 transition flex items-center gap-1">
            Bekijk per medewerker
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </span>
    </a>

</div>
