<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePriceCategory extends Model
{
    protected $fillable = ['code', 'name', 'min_price', 'max_price', 'is_active'];
    
    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getRangeAttribute(): string
    {
        $min = number_format($this->min_price, 0, ',', '.');
        $max = $this->max_price > 99999999999 ? '~' : number_format($this->max_price, 0, ',', '.');
        return "Rp {$min} - Rp {$max}";
    }
}
