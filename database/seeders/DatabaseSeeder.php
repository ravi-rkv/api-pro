<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::create([
            'uid' => 'ADM000001',
            'user_name' => 'Admin',
            'email' => 'ravi.verma@ciphersquare.tech',
            'mobile' => '7355988863',
            'dob' => Carbon::now()->toDate(),
            'city' => 'Delhi',
            'state' => 'Delhi',
            'country' => 'India (IN)',
            'address' => 'Kirti nagar , New Delhi , India',
            'password' => encrypt_pass('1234560987', ['uid' => 'ADM000001', 'created_at' => Carbon::now()->toDateTimeString()]),
            'avatar' => 'assets/image/avatar/user.png',
            'role_id' => 1,
            'twofa_status' => 0,
            'twofa_config' => 1,
            'is_deleted' => 0,
            'account_status' => 'ACTIVE',
            'created_at' => Carbon::now()->toDateTimeString(),
            'created_by' => 'ADM000001'
        ]);

        $roles = [
            [
                'role_name' => 'Admin',
                'role_desc' => 'Administrator',
                'role_type' => 'USER'
            ],
            [
                'role_name' => 'User',
                'role_desc' => 'User',
                'role_type' => 'USER'
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}
