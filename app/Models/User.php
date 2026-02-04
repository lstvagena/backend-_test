<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    // Tells Laravel to use the "tenant" database connection for this model
    protected $connection = 'tenant';

    // These fields are allowed to be filled using mass assignment (User::create)
    protected $fillable = ['name', 'email', 'password'];

    // These fields will be hidden when returning user data (for security)
    protected $hidden = ['password', 'remember_token'];

    // Automatically converts these fields to proper data types
    protected $casts = [
        // Converts email_verified_at into a DateTime object
        'email_verified_at' => 'datetime',

        // Automatically hashes the password before saving to database
        'password' => 'hashed',
    ];
}
