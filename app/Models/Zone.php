<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['code', 'name', 'description', 'provinces', 'is_active'];
    
    protected $casts = ['is_active' => 'boolean'];
}
