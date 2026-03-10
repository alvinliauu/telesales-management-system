<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TsiOption extends Model
{
    protected $fillable = ['coverage_type_id', 'tsi_amount', 'label', 'sort_order', 'is_active'];
    
    protected $casts = [
        'tsi_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function coverageType(): BelongsTo
    {
        return $this->belongsTo(CoverageType::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->tsi_amount, 0, ',', '.');
    }
}
