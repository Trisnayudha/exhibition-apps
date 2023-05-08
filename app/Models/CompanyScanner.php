<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyScanner extends Model
{
    protected $table = 'company_scan';

    protected $fillable = [
        'company_id',
        'users_id'
    ];
}
