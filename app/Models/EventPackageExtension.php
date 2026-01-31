<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPackageExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_package_id',
        'extension_id',
        'car_type_id',
        'rate_type',
        'rate_value',
    ];

    protected function casts(): array
    {
        return [
            'rate_value' => 'decimal:4',
        ];
    }

    public function eventPackage()
    {
        return $this->belongsTo(EventPackage::class);
    }

    public function extension()
    {
        return $this->belongsTo(Extension::class);
    }

    public function carType()
    {
        return $this->belongsTo(CarType::class);
    }

    public function getFormattedRateAttribute(): string
    {
        if ($this->rate_type === 'percentage') {
            return number_format($this->rate_value, 2) . '%';
        }
        return 'Rp ' . number_format($this->rate_value, 0, ',', '.');
    }
}
