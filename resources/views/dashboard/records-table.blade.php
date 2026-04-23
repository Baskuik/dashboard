{{-- Recent Records Table --}}
<div class="border border-white/8 bg-[#131928] rounded-xl p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-white">Recente records</h2>
            <p class="text-xs text-gray-500 mt-1">Jouw milieu-check registraties</p>
        </div>
        @if ($paginatedRecords->count() > 0)
            <div class="flex gap-2">
                <a href="{{ route('export.records') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="px-4 py-2 text-xs font-medium bg-green-600/20 text-green-300 border border-green-500/30 rounded-lg hover:bg-green-600/30 transition">
                    ↓ Data exporteren
                </a>
                <a href="{{ route('export.summary') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                    class="px-4 py-2 text-xs font-medium bg-blue-600/20 text-blue-300 border border-blue-500/30 rounded-lg hover:bg-blue-600/30 transition">
                    ↓ Samenvatting
                </a>
            </div>
        @endif
    </div>

    @php
        $buildSortUrl = function ($field) {
            $currentSort = request('sort_by', 'date');
            $currentDir = request('sort_dir', 'desc');
            $newDir = $currentSort === $field && $currentDir === 'asc' ? 'desc' : 'asc';

            $query = [];
            $query['sort_by'] = $field;
            $query['sort_dir'] = $newDir;
            if (request('search')) {
                $query['search'] = request('search');
            }
            if (request('from_date')) {
                $query['from_date'] = request('from_date');
            }
            if (request('to_date')) {
                $query['to_date'] = request('to_date');
            }
            if (request('min_cost')) {
                $query['min_cost'] = request('min_cost');
            }
            if (request('max_cost')) {
                $query['max_cost'] = request('max_cost');
            }

            return route('dashboard', $query);
        };
    @endphp

    @if ($paginatedRecords->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.04]">
                        <th
                            class="pb-3 pr-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                            <a href="{{ $buildSortUrl('date') }}"
                                class="flex items-center gap-1 hover:text-gray-400 transition">
                                Datum
                                <svg class="w-3 h-3 {{ request('sort_by') === 'date' ? (request('sort_dir') === 'asc' ? 'rotate-0' : 'rotate-180') : 'opacity-30' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16V4m0 0L3 8m0 0l4 4m10 0v12m0 0l4-4m0 0l-4-4" />
                                </svg>
                            </a>
                        </th>
                        <th
                            class="pb-3 pr-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                            <a href="{{ $buildSortUrl('action') }}"
                                class="flex items-center gap-1 hover:text-gray-400 transition">
                                Actie
                                <svg class="w-3 h-3 {{ request('sort_by') === 'action' ? (request('sort_dir') === 'asc' ? 'rotate-0' : 'rotate-180') : 'opacity-30' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16V4m0 0L3 8m0 0l4 4m10 0v12m0 0l4-4m0 0l-4-4" />
                                </svg>
                            </a>
                        </th>
                        <th class="pb-3 pr-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                            <a href="{{ $buildSortUrl('description') }}"
                                class="flex items-center gap-1 hover:text-gray-400 transition">
                                Omschrijving
                                <svg class="w-3 h-3 {{ request('sort_by') === 'description' ? (request('sort_dir') === 'asc' ? 'rotate-0' : 'rotate-180') : 'opacity-30' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16V4m0 0L3 8m0 0l4 4m10 0v12m0 0l4-4m0 0l-4-4" />
                                </svg>
                            </a>
                        </th>
                        <th
                            class="pb-3 pr-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                            <a href="{{ $buildSortUrl('worker') }}"
                                class="flex items-center gap-1 hover:text-gray-400 transition">
                                Medewerker
                                <svg class="w-3 h-3 {{ request('sort_by') === 'worker' ? (request('sort_dir') === 'asc' ? 'rotate-0' : 'rotate-180') : 'opacity-30' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16V4m0 0L3 8m0 0l4 4m10 0v12m0 0l4-4m0 0l-4-4" />
                                </svg>
                            </a>
                        </th>
                        <th
                            class="pb-3 pr-4 text-right text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                            <a href="{{ $buildSortUrl('time') }}"
                                class="flex items-center justify-end gap-1 hover:text-gray-400 transition">
                                Uren
                                <svg class="w-3 h-3 {{ request('sort_by') === 'time' ? (request('sort_dir') === 'asc' ? 'rotate-0' : 'rotate-180') : 'opacity-30' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16V4m0 0L3 8m0 0l4 4m10 0v12m0 0l4-4m0 0l-4-4" />
                                </svg>
                            </a>
                        </th>
                        <th
                            class="pb-3 text-right text-xs font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                            <a href="{{ $buildSortUrl('costs') }}"
                                class="flex items-center justify-end gap-1 hover:text-gray-400 transition">
                                Kosten
                                <svg class="w-3 h-3 {{ request('sort_by') === 'costs' ? (request('sort_dir') === 'asc' ? 'rotate-0' : 'rotate-180') : 'opacity-30' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16V4m0 0L3 8m0 0l4 4m10 0v12m0 0l4-4m0 0l-4-4" />
                                </svg>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.03]">
                    @foreach ($paginatedRecords as $record)
                        <tr class="hover:bg-white/[0.02] transition">
                            <td class="py-3 pr-4 text-gray-400 mono whitespace-nowrap text-xs">
                                {{ $record->date ? \Carbon\Carbon::parse($record->date)->format('d-m-Y') : '–' }}
                            </td>
                            <td class="py-3 pr-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/15 text-blue-300 border border-blue-500/20">
                                    {{ $record->action }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-gray-400 max-w-xs text-xs">
                                <span title="{{ $record->description }}">
                                    {{ Str::limit($record->description, 70) }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-gray-300 whitespace-nowrap text-xs">{{ $record->worker }}</td>
                            <td class="py-3 pr-4 text-right text-gray-400 mono text-xs whitespace-nowrap">
                                {{ number_format($record->time, 1, ',', '.') }}u
                            </td>
                            <td class="py-3 text-right font-medium text-cyan-400 mono text-xs whitespace-nowrap">
                                € {{ number_format($record->costs, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($paginatedRecords->hasPages())
            <div class="mt-6 pt-6 border-t border-white/5 flex items-center justify-between">
                <div class="text-xs text-gray-500">
                    Pagina {{ $paginatedRecords->currentPage() }} van {{ $paginatedRecords->lastPage() }}
                </div>
                <div class="flex gap-2">
                    @if ($paginatedRecords->onFirstPage())
                        <button disabled
                            class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-white/10 rounded-md opacity-50 cursor-not-allowed">
                            ← Vorige
                        </button>
                    @else
                        <a href="{{ $paginatedRecords->previousPageUrl() }}&sort_by={{ request('sort_by', 'date') }}&sort_dir={{ request('sort_dir', 'desc') }}"
                            class="px-3 py-1.5 text-xs font-medium text-gray-400 hover:text-white border border-white/10 hover:border-white/20 rounded-md transition">
                            ← Vorige
                        </a>
                    @endif

                    @if ($paginatedRecords->hasMorePages())
                        <a href="{{ $paginatedRecords->nextPageUrl() }}&sort_by={{ request('sort_by', 'date') }}&sort_dir={{ request('sort_dir', 'desc') }}"
                            class="px-3 py-1.5 text-xs font-medium text-gray-400 hover:text-white border border-white/10 hover:border-white/20 rounded-md transition">
                            Volgende →
                        </a>
                    @else
                        <button disabled
                            class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-white/10 rounded-md opacity-50 cursor-not-allowed">
                            Volgende →
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @else
        <div class="py-12 text-center">
            <p class="text-gray-600 text-sm">Geen records gevonden. Upload een Excel-bestand om te beginnen.</p>
        </div>
    @endif
</div>
