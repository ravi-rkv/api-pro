<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestApiLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'request_id',
        'uid',
        'method',
        'url',
        'headers',
        'payload',
        'response',
        'status_code',
    ];

    protected $casts = [
        'payload' => 'json',
        'response' => 'json',
    ];
}
