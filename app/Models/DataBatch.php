<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'month',
        'status',
        'total_records',
        'uw_approved_count',
        'uw_rejected_count',
        'marketing_approved_count',
        'sent_count',
        'success_count',
        'failed_count',
        'created_by',
        'uw_approved_by',
        'uw_approved_at',
        'marketing_approved_by',
        'marketing_approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'uw_approved_at' => 'datetime',
            'marketing_approved_at' => 'datetime',
        ];
    }

    // Status constants
    const STATUS_PENDING_UW = 'pending_uw';
    const STATUS_UW_APPROVED = 'uw_approved';
    const STATUS_UW_REJECTED = 'uw_rejected';
    const STATUS_PENDING_MARKETING = 'pending_marketing';
    const STATUS_MARKETING_APPROVED = 'marketing_approved';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';

    public function renewalData()
    {
        return $this->hasMany(RenewalData::class, 'batch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function uwApprover()
    {
        return $this->belongsTo(User::class, 'uw_approved_by');
    }

    public function marketingApprover()
    {
        return $this->belongsTo(User::class, 'marketing_approved_by');
    }

    public function getMonthLabelAttribute(): string
    {
        return \Carbon\Carbon::parse($this->month . '-01')->format('F Y');
    }

    public function updateCounts(): void
    {
        $this->update([
            'total_records' => $this->renewalData()->count(),
            'uw_approved_count' => $this->renewalData()->where('workflow_status', 'uw_approved')->count(),
            'uw_rejected_count' => $this->renewalData()->where('workflow_status', 'uw_rejected')->count(),
            'marketing_approved_count' => $this->renewalData()->where('workflow_status', 'marketing_approved')->count(),
            'sent_count' => $this->renewalData()->where('workflow_status', 'sent')->count(),
            'success_count' => $this->renewalData()->where('workflow_status', 'success')->count(),
            'failed_count' => $this->renewalData()->where('workflow_status', 'failed')->count(),
        ]);
    }

    public static function generateBatchNumber(string $month): string
    {
        $monthFormatted = str_replace('-', '', $month);
        $count = self::where('month', $month)->count() + 1;
        return sprintf('BATCH-%s-%03d', $monthFormatted, $count);
    }
}
