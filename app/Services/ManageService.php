<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ManageService
{


    public function getUserList($params)
    {


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
}
