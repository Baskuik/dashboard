<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Record extends Model
{
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
        'date'  => 'date',
        'time'  => 'decimal:2',
        'costs' => 'decimal:2',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }
}