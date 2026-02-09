<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenewalData extends Model
{
    use HasFactory;

    protected $table = 'renewal_data';

    protected $fillable = [
        'renewal_event_id',
        'upload_log_id',
        'no_polis',
        'no_kontrak',
        'nama_tertanggung',
        'no_hp',
        'email',
        'ano',
        'merk_kendaraan',
        'tahun_kendaraan',
        'jenis_coverage',
        'tsi',
        'premi',
        'start_date',
        'end_date',
        'agent',
        'cabang',
        'raw_data',
        // Partner fields
        'partner_status',
        'partner_request_id',
        'partner_error_message',
        'partner_error_code',
        'partner_sent_at',
        'partner_retry_count',
        // Workflow fields
        'workflow_status',
        'batch_id',
        'batch_month',
        'uw_approved_by',
        'uw_approved_at',
        'uw_notes',
        'uw_rejection_reason',
        'marketing_approved_by',
        'marketing_approved_at',
        'marketing_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'tsi' => 'decimal:2',
            'premi' => 'decimal:2',
            'raw_data' => 'array',
            'partner_sent_at' => 'datetime',
            'uw_approved_at' => 'datetime',
            'marketing_approved_at' => 'datetime',
        ];
    }

    // Workflow status constants
    const STATUS_PENDING_UW = 'pending_uw';
    const STATUS_UW_APPROVED = 'uw_approved';
    const STATUS_UW_REJECTED = 'uw_rejected';
    const STATUS_PENDING_MARKETING = 'pending_marketing';
    const STATUS_MARKETING_APPROVED = 'marketing_approved';
    const STATUS_SENT = 'sent';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    public function renewalEvent()
    {
        return $this->belongsTo(RenewalEvent::class);
    }

    public function uploadLog()
    {
        return $this->belongsTo(UploadLog::class);
    }

    public function batch()
    {
        return $this->belongsTo(DataBatch::class, 'batch_id');
    }

    public function uwApprover()
    {
        return $this->belongsTo(User::class, 'uw_approved_by');
    }

    public function marketingApprover()
    {
        return $this->belongsTo(User::class, 'marketing_approved_by');
    }

    // Scopes
    public function scopePendingUw($query)
    {
        return $query->where('workflow_status', self::STATUS_PENDING_UW);
    }

    public function scopeUwApproved($query)
    {
        return $query->where('workflow_status', self::STATUS_UW_APPROVED);
    }

    public function scopePendingMarketing($query)
    {
        return $query->where('workflow_status', self::STATUS_PENDING_MARKETING);
    }

    public function scopeMarketingApproved($query)
    {
        return $query->where('workflow_status', self::STATUS_MARKETING_APPROVED);
    }

    public function scopeByMonth($query, string $month)
    {
        return $query->where('batch_month', $month);
    }

    // Helper methods
    public function getWorkflowStatusLabelAttribute(): string
    {
        return match($this->workflow_status) {
            self::STATUS_PENDING_UW => 'Pending UW Review',
            self::STATUS_UW_APPROVED => 'UW Approved',
            self::STATUS_UW_REJECTED => 'UW Rejected',
            self::STATUS_PENDING_MARKETING => 'Pending Marketing',
            self::STATUS_MARKETING_APPROVED => 'Marketing Approved',
            self::STATUS_SENT => 'Sent to Partner',
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FAILED => 'Failed',
            default => ucfirst($this->workflow_status),
        };
    }

    public function getWorkflowStatusColorAttribute(): string
    {
        return match($this->workflow_status) {
            self::STATUS_PENDING_UW => 'secondary',
            self::STATUS_UW_APPROVED => 'dark',
            self::STATUS_UW_REJECTED => 'danger',
            self::STATUS_PENDING_MARKETING => 'secondary',
            self::STATUS_MARKETING_APPROVED => 'dark',
            self::STATUS_SENT => 'info',
            self::STATUS_SUCCESS => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'secondary',
        };
    }
}
