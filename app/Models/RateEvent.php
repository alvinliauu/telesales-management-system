<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'priority',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(RateEventRule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('start_date', '>', now());
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => '<span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded">Draft</span>',
            self::STATUS_ACTIVE => $this->isActive() 
                ? '<span class="px-2 py-1 text-xs bg-green-200 text-green-700 rounded">Active</span>'
                : '<span class="px-2 py-1 text-xs bg-blue-200 text-blue-700 rounded">Scheduled</span>',
            self::STATUS_EXPIRED => '<span class="px-2 py-1 text-xs bg-yellow-200 text-yellow-700 rounded">Expired</span>',
            self::STATUS_CANCELLED => '<span class="px-2 py-1 text-xs bg-red-200 text-red-700 rounded">Cancelled</span>',
            default => '<span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded">Unknown</span>',
        };
    }
}
