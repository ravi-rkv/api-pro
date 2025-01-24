<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'uid',
        'name',
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
        'ref_id',
        'account_status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];


    protected $hidden = [
        'password'
    ];

    public function role(): HasOne
    {
        return $this->hasOne(Role::class, 'role_id', 'role_id');
    }

    public static function getDetail($uid)
    {
        return self::with('role')->where(['uid' => $uid])->first()->toArray();
    }

    public static function userDetail($uid)
    {
        return (array) DB::table('users')
            ->select('users.*', DB::raw("CONCAT('" . url('/') . "', '/storage/', users.avatar) as avatar"), 'roles.role_name', 'roles.role_type')
            ->join('roles', 'users.role_id', '=', 'roles.role_id')
            ->where(['users.uid' => $uid])
            ->first();
    }

    public static function userFullDetail($uid)
    {
        return (array) DB::table('users')
            ->select(
                'users.uid',
                'users.name',
                'users.email',
                'users.mobile',
                'users.gender',
                DB::raw('COALESCE(users.dob, "NA") AS dob'),
                DB::raw("CONCAT('" . url('/') . "', '/storage/', users.avatar) as avatar"),
                DB::raw('COALESCE(users.city, "NA") AS city'),
                DB::raw('COALESCE(users.state, "NA") AS state'),
                DB::raw('COALESCE(users.country, "NA") AS country'),
                DB::raw('COALESCE(users.address, "NA") AS address'),
                'users.twofa_status',
                'users.role_id',
                'roles.role_name',
                'users.account_status',
                'users.created_at'
            )
            ->join('roles', 'users.role_id', '=', 'roles.role_id')
            ->where(['users.uid' => $uid])
            ->first();
    }
}
