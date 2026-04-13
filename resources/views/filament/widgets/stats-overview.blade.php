<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">{{ __('Totaal Records') }}</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_records'] }}</p>
        <p class="text-xs text-green-600 mt-1">↑ Geïmporteerd</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">{{ __('Totale Kosten') }}</h3>
        <p class="text-3xl font-bold text-gray-800">€ {{ number_format($stats['total_cost'], 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">→ In totaal</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">{{ __('Gem. Uren') }}</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['avg_hours'] }}h</p>
        <p class="text-xs text-gray-500 mt-1">→ Per record</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">{{ __('Medewerkers') }}</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_employees'] }}</p>
        <p class="text-xs text-gray-500 mt-1">→ Betrokken</p>
    </div>
</div>
