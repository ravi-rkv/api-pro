<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public $timestamp = false;



    protected $fillable = [
        'uid',
        'user_name',
        'email',
        'mobile',
        'dob',
        'city',
        'state',
        'country',
        'address',
        'avatar',
        'password',
        'role_id',
        'twofa_status',
        'twofa_config',
        'is_deleted',
        'account_status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];


    protected $hidden = [
        'password'
    ];
}
