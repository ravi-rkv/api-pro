<?php

namespace App\Http\Controllers\Admin\Manage;

use Illuminate\Http\Request;
use App\Services\ApiResponse;
use App\Services\ManageService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ManageController extends Controller
{
    private $manageService;
    public function __construct()
    {
        $this->manageService = new ManageService();
    }

    public function getUserList(Request $request)
    {
        $isAuthorized = checkIfActionAllowed('5', 'FETCH', $request['uid']);
        if (empty($isAuthorized)) {
            return ApiResponse::response('SNA', 'Unauthorized: Service not allowed.', [], 401);
        }

        $validator = Validator::make($request->all(), [
            'start' => 'required|integer|min:0',
            'length' => 'required|integer|min:1|max:100',
            'search.value' => 'nullable|string|max:255',
            'filter' => 'nullable|array',
            'order' => 'nullable|array',
            'order.*.column' => 'required_with:order|integer|min:0',
            'order.*.dir' => 'required_with:order|in:asc,desc',
        ], [
            'start.required' => 'The start parameter is required.',
            'start.integer' => 'The start parameter must be an integer.',
            'start.min' => 'The start parameter must be at least 0.',
            'length.required' => 'The length parameter is required.',
            'length.integer' => 'The length parameter must be an integer.',
            'length.min' => 'The length parameter must be at least 1.',
            'length.max' => 'The length parameter must not exceed 100.',
            'search.value.string' => 'The search value must be a string.',
            'search.value.max' => 'The search value may not exceed 255 characters.',
            'filter.array' => 'The filter parameter must be an array.',
            'order.array' => 'The order parameter must be an array.',
            'order.*.column.required_with' => 'The column index is required when order is provided.',
            'order.*.column.integer' => 'The column index must be an integer.',
            'order.*.column.min' => 'The column index must be at least 0.',
            'order.*.dir.required_with' => 'The sort direction is required when order is provided.',
            'order.*.dir.in' => 'The sort direction must be either "asc" or "desc".',
        ]);

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->manageService->getUserList($request->all());
    }

    public function getUserDetailByUid(Request $request)
    {
        $isAuthorized = checkIfActionAllowed('5', 'FETCH', $request['uid']);
        if (empty($isAuthorized)) {
            return ApiResponse::response('SNA', 'Unauthorized: Service not allowed.', [], 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ], [
            'user_id.required' => 'The user id field is required.',
        ]);

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        $userDetail = User::where('uid', $request['user_id'])->first();

        if (!empty($userDetail)) {
            return ApiResponse::response('RCS', 'User detail fetched successfully.', $userDetail, 200);
        } else {
            return ApiResponse::response('IRD', 'Unable to get requested user detail.', [], 200);
        }
    }

    public function addUserDetail(Request $request)
    {
        $isAuthorized = checkIfActionAllowed('5', 'ADD', $request['uid']);
        if (empty($isAuthorized)) {
            return ApiResponse::response('SNA', 'Unauthorized: Service not allowed.', [], 401);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'mobile' => [
                    'required',
                    'regex:/^[6-9]\d{9}$/',
                    'unique:users,mobile',
                ],
                'gender' => 'required|in:MALE,FEMALE,OTHERS',
                'dob' => 'required|date|before:' . now()->subYears(12)->toDateString(),
                'city' => 'required|min:3|max:100',
                'state' => 'required|min:3|max:100',
                'country' => 'required|min:3|max:100',
                'address' => 'required|min:10|max:250',
                'role_id' => 'required|exists:roles,role_id',
            ],
            [
                'name.required' => 'The name field is required.',
                'name.string' => 'The name must be a valid string.',
                'name.max' => 'The name must not exceed 255 characters.',
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email has already been taken.',
                'email.max' => 'The email must not exceed 255 characters.',
                'mobile.required' => 'The mobile number is required.',
                'mobile.regex' => 'The mobile number must be a valid 10-digit number starting with 6-9.',
                'mobile.unique' => 'The mobile number has already been taken.',
                'gender.required' => 'The gender field is required.',
                'gender.in' => 'The gender must be one of MALE, FEMALE, or OTHERS.',
                'dob.required' => 'The date of birth field is required.',
                'dob.date' => 'The date of birth must be a valid date.',
                'dob.before' => 'The date of birth must indicate an age of at least 12 years.',
                'city.required' => 'The city field is required.',
                'city.min' => 'The city must have at least 3 characters.',
                'city.max' => 'The city must not exceed 100 characters.',
                'state.required' => 'The state field is required.',
                'state.min' => 'The state must have at least 3 characters.',
                'state.max' => 'The state must not exceed 100 characters.',
                'country.required' => 'The country field is required.',
                'country.min' => 'The country must have at least 3 characters.',
                'country.max' => 'The country must not exceed 100 characters.',
                'address.required' => 'The address field is required.',
                'address.min' => 'The address must have at least 10 characters.',
                'address.max' => 'The address must not exceed 250 characters.',
                'role_id.required' => 'The role  field is required.',
                'role_id.exists' => 'The selected role does not exist.',
            ]
        );

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->manageService->addUserDetail($request->all());
    }

    public function  updateUserDetail(Request $request)
    {
        $isAuthorized = checkIfActionAllowed('5', 'UPDATE', $request['uid']);
        if (empty($isAuthorized)) {
            return ApiResponse::response('SNA', 'Unauthorized: Service not allowed.', [], 401);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'request_type' => 'required|in:BASIC_DETAIL,ACCOUNT_STATUS,2FA_STATUS',
                'user_id' => 'required',
                // basic detail
                'name' => 'required_if:request_type,BASIC_DETAIL',
                'gender' => 'required_if:request_type,BASIC_DETAIL|in:MALE,FEMALE,OTHERS',
                'dob' => 'required_if:request_type,BASIC_DETAIL|date|before: ' . now()->subYears(12)->toDateString(),
                'city' => 'required_if:request_type,BASIC_DETAIL|min:3',
                'state' => 'required_if:request_type,BASIC_DETAIL|min:3',
                'country' => 'required_if:request_type,BASIC_DETAIL|min:3|max:100',
                'address' => 'required_if:request_type,BASIC_DETAIL|min:10|max:250',
                // account status
                'account_status' => 'required_if:request_type,ACCOUNT_STATUS|in:ACTIVE,INACTIVE,BLOCKED',
                //2FA status
                'twofa_status' => 'required_if:request_type,2FA_STATUS|in:ENABLE,DISABLE'
            ],
            [
                // General Request Type
                'request_type.required' => 'The request type field is required.',
                'request_type.in' => 'The request type must be one of the following: BASIC_DETAIL, ACCOUNT_STATUS, or 2FA_STATUS.',
                'user_id.required' => 'The user id field is required.',

                // Basic Detail
                'name.required_if' => 'The name field is required.',
                'gender.required_if' => 'The gender field is required.',
                'gender.in' => 'The gender must be one of the following: MALE, FEMALE, or OTHERS.',
                'dob.required_if' => 'The date of birth (DOB) field is required.',
                'dob.date' => 'The date of birth (DOB) must be a valid date.',
                'dob.before' => 'The date of birth (DOB) must indicate an age of at least 12 years.',
                'city.required_if' => 'The city field is required.',
                'city.min' => 'The city must be at least 3 characters long.',
                'state.required_if' => 'The state field is required.',
                'state.min' => 'The state must be at least 3 characters long.',
                'country.required_if' => 'The country field is required.',
                'country.min' => 'The country must be at least 3 characters long.',
                'country.max' => 'The country must not exceed 100 characters.',
                'address.required_if' => 'The address field is required.',
                'address.min' => 'The address must be at least 10 characters long.',
                'address.max' => 'The address must not exceed 250 characters.',

                // Account Status
                'account_status.required_if' => 'The account status field is required.',
                'account_status.in' => 'The account status must be one of the following: ACTIVE, INACTIVE, or BLOCKED.',

                // 2FA Status
                'twofa_status.required_if' => 'The 2FA status field is required.',
                'twofa_status.in' => 'The 2FA status must be either ENABLE or DISABLE.',
            ]
        );

        if ($validator->fails() == TRUE) {
            return ApiResponse::response('IRD', $validator->messages()->first(), [], 400);
        }

        return $this->manageService->updateUserDetail($request->all());
    }
}
