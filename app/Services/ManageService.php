<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManageService
{


    public function getUserList($params)
    {
        if (empty($params)) {
            ApiResponse::response('IRD', 'Invalid request detail.', [], 400);
        }

        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? null;
        $filters = $params['filter'] ?? [];
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDirection = $params['order'][0]['dir'] ?? 'asc';


        $select = ['users.uid', 'users.name', 'users.email', 'users.mobile', 'users.gender', 'roles.role_name AS role', 'users.account_status'];

        $query = User::select($select)->where(['users.is_deleted' => 0])->join('roles', 'roles.role_id', '=', 'users.role_id');


        $filterColumns = [
            'uid' => 'users.uid',
            'name' => 'users.name',
            'email' => 'users.email',
            'mobile' => 'users.mobile',
            'gender' => 'users.gender',
            'role' => 'roles.role_name',
            'account_status' => 'users.account_status'
        ];
        $orderColumn = isset($select[$orderColumnIndex]) ? $select[$orderColumnIndex] : 'uid';


        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue, $filterColumns) {
                foreach ($filterColumns as $column) {
                    $q->orWhere($column, 'like', '%' . $searchValue . '%');
                }
            });
        }

        // Apply specific filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (isset($filterColumns[$key])) {
                    $query->where($filterColumns[$key], $value);
                }
            }
        }

        $totalRecords = User::where(['users.is_deleted' => 0])->count() ?? 0;

        $filteredQuery = clone $query;
        $recordsFiltered = $filteredQuery->count() ?? 0;

        $query->orderBy($orderColumn, $orderDirection);
        $query->offset($start)->limit($length);

        $data = $query->get() ?? [];

        return ApiResponse::response('RCS', 'User fetched successfully', ['data' => $data, 'recordsTotal' => $totalRecords, 'recordsFiltered' => $recordsFiltered], 200);
    }

    public function addUserDetail($params)
    {
        if (empty($params)) {
            return ApiResponse::response('IRD', 'Invalid request detail.', [], 400);
        }

        if ($params['rid'] != '1' && $params['role_id'] == '1') {
            return ApiResponse::response('UAU', 'Unauthorize : Not authorized to create user with selected role', [], 401);
        }

        $uid = generateUserId($params['role_id']);

        if (!empty($uid)) {

            $password = encrypt_pass(, ['uid' => $uid, 'created_at' => $registeredOn]);


        }
        $userData = [
            'uid' => $uid,
            'name' => $params['name'],
            'email' => $params['email'],
            'mobile' => $params['mobile'],
            'gender' => $params['gender'],
            'dob' => $params['dob'],
            'city' => $params['city'],
            'state' => $params['state'],
            'country' => $params['country'],
            'address' => $params['address'],
            'password' => $password,
            'avatar' => 'assets/image/avatar/user.png',
            'role_id' => $params['role_id'],
            'twofa_status' => '1',
            'twofa_config' => '1',
            'account_status' => 'ACTIVE',
            'created_at' => Carbon::now()->toDateTimeString(),
            'created_by' => $uid
        ];
    }

    public function updateUserDetail($params)
    {
        if (empty($params) || empty($params['user_id']) || empty($params['request_type']) || !in_array($params['request_type'], ['BASIC_DETAIL', 'ACCOUNT_STATUS', '2FA_STATUS'])) {
            return  ApiResponse::response('IRD', 'Invalid request detail.', [], 400);
        }

        $userDetail = User::where('uid', $params['user_id'])->first();

        if (empty($userDetail)) {
            return ApiResponse::response('IRD', 'Unable to get requested user detail.', [], 400);
        }
    }
}
