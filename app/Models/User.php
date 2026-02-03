<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'tenant';  // Dynamic DB

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password'];
}
