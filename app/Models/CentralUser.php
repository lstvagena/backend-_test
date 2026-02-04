<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentralUser extends Model
{
   // protected $connection = 'mysql';  // main_db
   protected $fillable = ['email', 'company_slug', 'tenant_user_id'];
}
