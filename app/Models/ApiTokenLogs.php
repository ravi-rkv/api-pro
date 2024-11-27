<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiTokenLogs extends Model
{

    protected $fillable = [
        'uid',
        'token',
        'ip',
        'is_active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
}
