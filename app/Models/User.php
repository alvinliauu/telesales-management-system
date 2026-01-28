<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function createdEvents()
    {
        return $this->hasMany(RenewalEvent::class, 'created_by');
    }

    public function assignedRenewals()
    {
        return $this->hasMany(RenewalData::class, 'assigned_to');
    }

    public function callHistories()
    {
        return $this->hasMany(CallHistory::class, 'called_by');
    }
}
