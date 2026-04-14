<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboardWidget extends Model
{
    protected $fillable = [
        'user_id',
        'widget_key',
        'order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all available widgets that can be added
     */
    public static function getAvailableWidgets(): array
    {
        return [
            [
                'key' => 'actions_per_month',
                'name' => 'Acties per Maand',
                'description' => 'Lijndiagram met het aantal acties per maand',
                'icon' => 'chart-line',
            ],
            [
                'key' => 'costs_per_month',
                'name' => 'Kosten per Maand',
                'description' => 'Lijndiagram met de totale kosten per maand',
                'icon' => 'currency-euro',
            ],
            [
                'key' => 'costs_per_employee',
                'name' => 'Kosten per Medewerker',
                'description' => 'Staafdiagram met kosten per medewerker',
                'icon' => 'users',
            ],
            [
                'key' => 'actions_by_type',
                'name' => 'Acties naar Type',
                'description' => 'Donutdiagram met acties per type',
                'icon' => 'target',
            ],
        ];
    }

    /**
     * Get SVG icon for a widget
     */
    public static function getWidgetIcon(string $iconKey): string
    {
        $icons = [
            'chart-line' => '<svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
            'currency-euro' => '<svg class="w-12 h-12 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'users' => '<svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M7 20H2v-2a3 3 0 015.856-1.487M15 7a3 3 0 11-6 0 3 3 0 016 0zM6 12a1 1 0 11-2 0 1 1 0 012 0zM20 12a1 1 0 11-2 0 1 1 0 012 0z"/></svg>',
            'target' => '<svg class="w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
        ];

        return $icons[$iconKey] ?? $icons['chart-line'];
    }
}
