<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateEventRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_event_id',
        'vehicle_type_id',
        'coverage_type_id',
        'zone_id',
        'vehicle_price_category_id',
        'transaction_type_id',
        'tsi_option_id',
        'override_type',
        'custom_rate',
        'flat_amount',
        'percentage_value',
        'tsi_add_amount',
        'discount_percent',
        'only_if_previous_exists',
        'previous_condition',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'custom_rate' => 'decimal:4',
        'flat_amount' => 'decimal:2',
        'percentage_value' => 'decimal:4',
        'tsi_add_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'only_if_previous_exists' => 'boolean',
        'is_active' => 'boolean',
    ];

    const OVERRIDE_USE_BATAS_BAWAH = 'use_batas_bawah';
    const OVERRIDE_USE_BATAS_ATAS = 'use_batas_atas';
    const OVERRIDE_USE_CUSTOM_RATE = 'use_custom_rate';
    const OVERRIDE_USE_FLAT_AMOUNT = 'use_flat_amount';
    const OVERRIDE_USE_PERCENTAGE = 'use_percentage';
    const OVERRIDE_FOLLOW_PREVIOUS = 'follow_previous';
    const OVERRIDE_TSI_ADD_AMOUNT = 'tsi_add_amount';
    const OVERRIDE_NO_CHARGE = 'no_charge';

    public static function overrideTypeOptions(): array
    {
        return [
            self::OVERRIDE_USE_BATAS_BAWAH => 'Gunakan Batas Bawah OJK',
            self::OVERRIDE_USE_BATAS_ATAS => 'Gunakan Batas Atas OJK',
            self::OVERRIDE_USE_CUSTOM_RATE => 'Rate Custom (%)',
            self::OVERRIDE_USE_FLAT_AMOUNT => 'Flat Amount (Rp)',
            self::OVERRIDE_USE_PERCENTAGE => 'Percentage dari TSI (%)',
            self::OVERRIDE_FOLLOW_PREVIOUS => 'Ikut Tahun Sebelumnya',
            self::OVERRIDE_TSI_ADD_AMOUNT => 'TSI Tahun Lalu + Nominal',
            self::OVERRIDE_NO_CHARGE => 'Gratis (Rp 0)',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(RateEvent::class, 'rate_event_id');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function coverageType(): BelongsTo
    {
        return $this->belongsTo(CoverageType::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function vehiclePriceCategory(): BelongsTo
    {
        return $this->belongsTo(VehiclePriceCategory::class);
    }

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function tsiOption(): BelongsTo
    {
        return $this->belongsTo(TsiOption::class);
    }

    public function getOverrideDescriptionAttribute(): string
    {
        return match($this->override_type) {
            self::OVERRIDE_USE_BATAS_BAWAH => 'Batas Bawah OJK',
            self::OVERRIDE_USE_BATAS_ATAS => 'Batas Atas OJK',
            self::OVERRIDE_USE_CUSTOM_RATE => 'Rate: ' . number_format($this->custom_rate, 2) . '%',
            self::OVERRIDE_USE_FLAT_AMOUNT => 'Flat: Rp ' . number_format($this->flat_amount, 0, ',', '.'),
            self::OVERRIDE_USE_PERCENTAGE => number_format($this->percentage_value, 2) . '% x TSI',
            self::OVERRIDE_FOLLOW_PREVIOUS => 'Ikut Tahun Lalu',
            self::OVERRIDE_TSI_ADD_AMOUNT => 'TSI + Rp ' . number_format($this->tsi_add_amount, 0, ',', '.'),
            self::OVERRIDE_NO_CHARGE => 'Gratis',
            default => '-',
        };
    }
}
