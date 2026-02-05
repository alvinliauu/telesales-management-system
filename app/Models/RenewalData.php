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
        ];
    }

    public function renewalEvent()
    {
        return $this->belongsTo(RenewalEvent::class);
    }

    public function uploadLog()
    {
        return $this->belongsTo(UploadLog::class);
    }

    public function scopePendingPartner($query)
    {
        return $query->where('partner_status', 'pending');
    }

    public function scopeFailedPartner($query)
    {
        return $query->where('partner_status', 'failed');
    }

    public function scopeSuccessPartner($query)
    {
        return $query->where('partner_status', 'success');
    }
}
