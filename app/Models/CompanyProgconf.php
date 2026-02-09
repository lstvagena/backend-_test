<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProgconf extends Model
{
    protected $table = 'company_progconf';
    protected $fillable = ['comcde', 'appcde', 'fcon'];
}

