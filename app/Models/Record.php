<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Record extends Model
{
    protected $primaryKey = 'record_id';

    protected $fillable = [
        'upload_id',
        'user_id',
        'date',
        'action',
        'description',
        'worker',
        'time',
        'costs',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'decimal:2',
        'costs' => 'decimal:2',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'upload_id', 'bestand_id');
    }

    /**
     * Get validation rules for Record data
     * Required fields: action, date, worker, time, costs
     */
    public static function validationRules(): array
    {
        return [
            'upload_id' => 'required|integer|exists:uploads,bestand_id',
            'user_id' => 'required|integer|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            'action' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'worker' => 'required|string|max:255',
            'time' => 'required|numeric|min:0|max:24',
            'costs' => 'required|numeric|min:0|max:999999.99',
        ];
    }

    /**
     * Validate a single record before creation
     */
    public static function validateRecord(array $data): array
    {
        $validator = \Illuminate\Support\Facades\Validator::make(
            $data,
            self::validationRules()
        );

        return $validator->validated();
    }
}