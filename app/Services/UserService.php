<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;

class UserService
{

    public function updateUserDetail($params)
    {
        if (empty($params) && $params['uid']) {
            return  ApiResponse::response('IRD', 'Invalid request detail.', [], 400);
        }

        $userDetail = User::userDetail($params['uid']);
        if (empty($userDetail)) {
            return ApiResponse::response('ERR', 'Unable to identify user details.', [], 400);
        }

        $updateDetail = User::where(['uid' => $userDetail['uid']])->update([
            'name' => $params['name'],
            'gender' => $params['gender'],
            'dob' => $params['dob'],
            'city' => $params['city'],
            'state' => $params['state'],
            'country' => $params['country'],
            'address' => $params['address'],
            'updated_at' => Carbon::now()->toDateTimeString(),
            'updated_by' => $userDetail['uid']
        ]);

        if ($updateDetail) {
            return ApiResponse::response('RCS', 'Details updated successfully.', [], 200);
        } else {
            return ApiResponse::response('ERR', 'Something went wrong , try again later.', [], 200);
        }
    }
}
