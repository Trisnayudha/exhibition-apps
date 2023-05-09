<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyQuestioner extends Model
{
    protected $table = 'company_questioner';

    protected $fillable = [
        'company_id',
    ];
}
