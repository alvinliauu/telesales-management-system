<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'renewal_data_id',
        'called_by',
        'called_at',
        'result',
        'notes',
        'callback_scheduled',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
            'callback_scheduled' => 'datetime',
        ];
    }

    public function renewalData()
    {
        return $this->belongsTo(RenewalData::class);
    }

    public function calledByUser()
    {
        return $this->belongsTo(User::class, 'called_by');
    }

    public function getResultBadgeAttribute(): string
    {
        return match($this->result) {
            'answered' => 'success',
            'no_answer' => 'warning',
            'busy' => 'warning',
            'voicemail' => 'info',
            'wrong_number' => 'danger',
            'callback_requested' => 'primary',
            'interested' => 'info',
            'renewed' => 'success',
            'declined' => 'danger',
            default => 'secondary',
        };
    }

    public function getResultLabelAttribute(): string
    {
        return match($this->result) {
            'answered' => 'Answered',
            'no_answer' => 'No Answer',
            'busy' => 'Busy',
            'voicemail' => 'Voicemail',
            'wrong_number' => 'Wrong Number',
            'callback_requested' => 'Callback Requested',
            'interested' => 'Interested',
            'renewed' => 'Renewed',
            'declined' => 'Declined',
            default => ucfirst($this->result),
        };
    }
}
