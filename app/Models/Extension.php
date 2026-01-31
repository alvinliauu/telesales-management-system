<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_main_coverage',
        'max_vehicle_age',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_main_coverage' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeMainCoverage($query)
    {
        return $query->where('is_main_coverage', true);
    }

    public function hasVehicleAgeLimit(): bool
    {
        return !is_null($this->max_vehicle_age);
    }

    public function eventPackageExtensions()
    {
        return $this->hasMany(EventPackageExtension::class);
    }
}
