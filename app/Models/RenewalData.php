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
        'no_kontrak',
        'ano',
        'end_date',
        'nama_tertanggung',
        'merk',
        'tipe',
        'tahun_kendaraan',
        'nilai_pertanggungan',
        'premi_comprehensive',
        'premi_tlo',
        'premi_comprehensive_extended',
        'no_polis',
        'wilayah',
        'jumlah_klaim',
        'tahun_renewal',
        'jaminan_existing',
        'no_telepon',
        'email',
        'call_status',
        'call_notes',
        'last_call_at',
        'callback_at',
        'selected_package',
        'agreed_premium',
        'assigned_to',
        'last_called_by',
    ];

    protected function casts(): array
    {
        return [
            'end_date' => 'date',
            'nilai_pertanggungan' => 'decimal:2',
            'premi_comprehensive' => 'decimal:2',
            'premi_tlo' => 'decimal:2',
            'premi_comprehensive_extended' => 'decimal:2',
            'agreed_premium' => 'decimal:2',
            'last_call_at' => 'datetime',
            'callback_at' => 'datetime',
        ];
    }

    public function renewalEvent()
    {
        return $this->belongsTo(RenewalEvent::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function lastCalledByUser()
    {
        return $this->belongsTo(User::class, 'last_called_by');
    }

    public function callHistories()
    {
        return $this->hasMany(CallHistory::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->call_status) {
            'pending' => 'secondary',
            'called' => 'info',
            'no_answer' => 'warning',
            'callback' => 'primary',
            'interested' => 'info',
            'renewed' => 'success',
            'declined' => 'danger',
            'invalid_contact' => 'dark',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->call_status) {
            'pending' => 'Pending',
            'called' => 'Called',
            'no_answer' => 'No Answer',
            'callback' => 'Callback',
            'interested' => 'Interested',
            'renewed' => 'Renewed',
            'declined' => 'Declined',
            'invalid_contact' => 'Invalid Contact',
            default => ucfirst($this->call_status),
        };
    }
}
