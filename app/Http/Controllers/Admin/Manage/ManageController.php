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
}
