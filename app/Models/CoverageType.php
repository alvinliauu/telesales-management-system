<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoverageType extends Model
{
    protected $fillable = [
        'code', 
        'name', 
        'category', 
        'has_tsi_options', 
        'has_ojk_rate', 
        'sort_order', 
        'is_active'
    ];
    
    protected $casts = [
        'has_tsi_options' => 'boolean',
        'has_ojk_rate' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tsiOptions(): HasMany
    {
        return $this->hasMany(TsiOption::class)->orderBy('sort_order');
    }

    public function scopeMain($query)
    {
        return $query->where('category', 'main');
    }

    public function scopeExtension($query)
    {
        return $query->where('category', 'extension');
    }
}
