<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'renewal_event_id',
        'filename',
        'original_filename',
        'total_rows',
        'success_rows',
        'failed_rows',
        'errors',
        'status',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
        ];
    }

    public function renewalEvent()
    {
        return $this->belongsTo(RenewalEvent::class);
    }

    public function uploadedByUser()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'processing' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'secondary',
        };
    }
}
