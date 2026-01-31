<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'renewal_event_id',
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function renewalEvent()
    {
        return $this->belongsTo(RenewalEvent::class);
    }

    public function packageExtensions()
    {
        return $this->hasMany(EventPackageExtension::class);
    }

    public function extensions()
    {
        return $this->belongsToMany(Extension::class, 'event_package_extensions')
            ->withPivot(['car_type_id', 'rate_type', 'rate_value'])
            ->withTimestamps();
    }

    public function getExtensionsByCarType($carTypeId)
    {
        return $this->packageExtensions()
            ->where('car_type_id', $carTypeId)
            ->with('extension')
            ->get();
    }

    public function hasMainCoverage(): bool
    {
        return $this->packageExtensions()
            ->whereHas('extension', fn($q) => $q->where('is_main_coverage', true))
            ->exists();
    }

    public function getMainCoverage()
    {
        return $this->packageExtensions()
            ->whereHas('extension', fn($q) => $q->where('is_main_coverage', true))
            ->with('extension')
            ->first();
    }
}
