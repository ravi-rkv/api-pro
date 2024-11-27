<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class RestApiLog extends Model
{

    protected $fillable = [
        'request_id',
        'uid',
        'method',
        'url',
        'headers',
        'payload',
        'response',
        'status_code',
        'ip',
        'duration'
    ];

    protected $casts = [
        'payload' => 'json',
        'response' => 'json',
    ];
}
