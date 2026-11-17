<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['snap_token'];

    protected function casts(): array
    {
        return ['gross_amount' => 'decimal:2', 'attempted_at' => 'datetime', 'paid_at' => 'datetime', 'checked_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
