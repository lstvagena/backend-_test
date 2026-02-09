<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLogFile extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        //'auditable_id',
        'old_value',
        'new_value',
        'url',
        'ip_address',
        'remarks',
        'user_agent',
        'module_name',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];
}
