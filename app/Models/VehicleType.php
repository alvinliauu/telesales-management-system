<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $fillable = [
        'code', 
        'name', 
        'default_rate_type', 
        'main_cover_rate_type', 
        'extension_rate_type', 
        'is_active'
    ];
    
    protected $casts = ['is_active' => 'boolean'];
}
