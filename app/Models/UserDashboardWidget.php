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
                'icon' => '📊',
            ],
            [
                'key' => 'costs_per_month',
                'name' => 'Kosten per Maand',
                'description' => 'Lijndiagram met de totale kosten per maand',
                'icon' => '💰',
            ],
            [
                'key' => 'costs_per_employee',
                'name' => 'Kosten per Medewerker',
                'description' => 'Staafdiagram met kosten per medewerker',
                'icon' => '👤',
            ],
            [
                'key' => 'actions_by_type',
                'name' => 'Acties naar Type',
                'description' => 'Donutdiagram met acties per type',
                'icon' => '🎯',
            ],
        ];
    }
}
