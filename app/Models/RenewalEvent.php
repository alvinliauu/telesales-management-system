<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenewalEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'event_type',
        'year',
        'month',
        'start_date',
        'end_date',
        'description',
        'status',
        'settings',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'settings' => 'array',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function renewalData()
    {
        return $this->hasMany(RenewalData::class);
    }

    public function uploadLogs()
    {
        return $this->hasMany(UploadLog::class);
    }

    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    public function getPeriodAttribute(): string
    {
        return $this->month_name . ' ' . $this->year;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getTotalDataAttribute(): int
    {
        return $this->renewalData()->count();
    }

    public function getRenewedCountAttribute(): int
    {
        return $this->renewalData()->where('call_status', 'renewed')->count();
    }
}
