<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempUserRegistration extends Model
{
    use HasFactory;

    protected $fillable = ['registration_id', 'name', 'email', 'mobile', 'gender', 'dob', 'city', 'state', 'country', 'address', 'password', 'is_verified', 'created_at', 'updated_at'];
}
