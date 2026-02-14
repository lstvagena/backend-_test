<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
    
class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'user_type_id',
        'login_attempts',
        'login_counts',
        'is_locked',
        'is_verified'
    ];

    protected $hidden = ['password'];

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }
}

