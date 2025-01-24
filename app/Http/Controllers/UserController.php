<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TabList;
use Illuminate\Http\Request;
use App\Services\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function getUserDetail(Request $request)
    {
        if (empty($request['uid']) || empty($request['token'])) {
            return ApiResponse::response('IRD', 'Invalid request details.', [], 400);
        }

        $userDetail = User::userFullDetail($request['uid']);
        if (!empty($userDetail)) {
            return ApiResponse::response('RCS', 'Detail fetched successfully.', $userDetail, 200);
        } else {
            return ApiResponse::response('ERR', 'Unable to get user detail.', [], 400);
        }
    }

    public function getAllowedPages(Request $request)
    {
        if (empty($request['uid']) || empty($request['token'])) {
            return ApiResponse::response('IRD', 'Invalid request details.', [], 400);
        }


        $allowedTabs = generateSidebar();
        if (!empty($allowedTabs)) {
            return ApiResponse::response('RCS', 'Detail fetched successfully.', $allowedTabs, 200);
        } else {
            return ApiResponse::response('ERR', 'Unable to get user detail.', [], 400);
        }
    }

    public function updateUserDetail(Request $request)
    {
        if (empty($request['uid']) || empty($request['token'])) {
            return ApiResponse::response('IRD', 'Invalid request details.', [], 400);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'gender' => 'required|in:MALE,FEMALE,OTHERS',
                'dob' => 'required|date|before: ' . now()->subYears(12)->toDateString(),
                'city' => 'required|min:3',
                'state' => 'required|min:3',
                'country' => 'required|min:3|max:100',
                'address' => 'required|min:10|max:250',
            ]
        );

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->userService->updateUserDetail($request->all());
    }
}
