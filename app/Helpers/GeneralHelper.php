<?php

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\TabList;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\TempUserRegistration;
use Illuminate\Support\Facades\Request;

if (!function_exists('encrypt_pass')) {
    function encrypt_pass($text, $data_array)
    {
        if (!empty($text) && is_string($text) && !empty($data_array)) {
            if (!empty($data_array['uid']) && !empty($data_array['created_at'])) {

                $encKey = config('cred.pass_enc_key');
                $iv = substr(($data_array['uid'] . date('Ymdhis', strtotime($data_array['created_at']))), 0, 16);

                $encryptedString = openssl_encrypt($text, 'AES-256-CBC', $encKey, 0, $iv);
                $encryptedPassword = base64_encode($encryptedString);

                return $encryptedPassword;
            }
        }
    }
}

if (!function_exists('decrypt_pass')) {
    function decrypt_pass($encText, $data_array)
    {
        if (!empty($encText) && is_string($encText) && !empty($data_array)) {
            if (!empty($data_array['uid']) && !empty($data_array['created_at'])) {

                $decodedText = base64_decode($encText);

                $encKey = config('cred.pass_enc_key');
                $iv = substr(($data_array['uid'] . date('Ymdhis', strtotime($data_array['created_at']))), 0, 16);

                $decryptedString = openssl_decrypt($decodedText, 'AES-256-CBC', $encKey, 0, $iv);
                return $decryptedString;
            }
        }
    }
}
if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('generateNewRegistrationId')) {
    function generateNewRegistrationId()
    {
        $regNo = 'REG' . date('Ymdhis') . strtoupper(generateRandomString());
        if ($regNo) {
            $checkIfExist = TempUserRegistration::where('registration_id', $regNo)->first();
            if ($checkIfExist) {
                return generateNewRegistrationId();
            } else {
                return $regNo;
            }
        }
    }
}

if (!function_exists('generateUserId')) {
    function generateUserId($userRoleId)
    {

        $userRoleId = (is_numeric($userRoleId) || ctype_digit($userRoleId)) ? trim($userRoleId) : '';

        if ($userRoleId) {

            $allowedUserRole = \App\Models\Role::where(['role_id' => $userRoleId])->first();
            if (!empty($allowedUserRole)) {

                $allowedUserRole = $allowedUserRole->toArray();
                $uidSequence = '';

                $getExistUid = \App\Models\User::select('uid')->where('role_id', $userRoleId)->orderBy('id', 'DESC')->first();

                if (!empty($getExistUid)) {

                    $lastUid = $getExistUid['uid'];
                    $checkInitials = substr($lastUid, 0, 3);

                    if (($checkInitials == $allowedUserRole['role_prefix'])) {
                        $uidSequence = substr($lastUid, 3);
                    } else {
                        $uidSequence = '0';
                    }
                } else {
                    $uidSequence = '0';
                }

                if (is_numeric($uidSequence)) {
                    $nextSequesce = $uidSequence + 1;

                    $numSeq = strlen($nextSequesce);

                    if ($numSeq < 6) {
                        $numeric_part = '';

                        for ($i = 0; $i < (6 - $numSeq); $i++) {
                            $numeric_part .= '0';
                        }

                        $numeric_part .= $nextSequesce;
                    } else if ($numSeq == 6) {
                        $numeric_part = $nextSequesce;
                    } else {
                        return false;
                    }

                    $uid = $allowedUserRole['role_prefix'] . $numeric_part;

                    $checkUidExist = \App\Models\User::where('uid', $uid)->first();

                    if (!$checkUidExist) {
                        return $uid;
                    }
                }
            }
        }
    }
}

if (!function_exists('getUserDataByToken')) {
    function getUserDataByToken($token = null)
    {
        $authToken = !empty($token) ? $token : Request::bearerToken();

        if (!empty($authToken)) {

            $key = config('cred.JWT_KEY');

            $tokenData = JWT::decode($authToken, new Key($key, 'HS256'));


            if (!empty($tokenData)) {
                $tokenData = json_decode(json_encode($tokenData), true);


                if (!empty($tokenData) && !empty($tokenData['uid'])) {
                    $userDetail = User::userFullDetail($tokenData['uid']);
                    if (!empty($userDetail)) {
                        return $userDetail;
                    }
                }
            }
        }
    }
}


if (!function_exists('getPageTabAccess')) {
    function getPageTabAccess($pageId, $result = null)
    {
        if (ctype_digit($pageId)) {
            $data = [];
            $userDetail = getUserDataByToken();
            if ($userDetail) {

                $getAllPermission = permissionListByType(null);
                if ($getAllPermission) {

                    $getTabData = TabList::where('tab_id', $pageId)->where('is_active', 1)->first();
                    if ($getTabData) {

                        if (in_array($getTabData['permission_id'], $getAllPermission)) {

                            if ($getTabData['parent_tab'] != 0) {

                                return getPageTabAccess($getTabData['parent_tab'], $result = null);
                            } else {
                                return true;
                            }
                        }
                    }
                }
            }
        }
    }
}

if (!function_exists('checkIfPermissionAllowed')) {
    function checkIfPermissionAllowed($permissionId, $type)
    {
        $permissionId = ctype_digit($permissionId) ? trim($permissionId) : "";
        $type = is_string($type) ? trim($type) : "";
        if ($permissionId != "" && $type != "") {
            $getPermissionListByType = permissionListByType($type);
            if ($getPermissionListByType) {
                if (in_array($permissionId, $getPermissionListByType)) {
                    return true;
                }
            }
        }
    }
}

if (!function_exists('getTabDataByType')) {
    function getTabDataByType($type)
    {
        if (is_string($type)) {
            $getPermissionListByType = permissionListByType($type);

            if ($getPermissionListByType) {
                $getTabListByType = TabList::getTabListByPerm($getPermissionListByType);
                if ($getTabListByType) {
                    return $getTabListByType;
                }
            }
        }
    }
}

if (!function_exists('permissionListByType')) {
    function permissionListByType($type = null)
    {
        $data = [];
        $userDetail = getUserDataByToken();
        if ($userDetail) {
            $getPermissionData = Permission::getAssignedPermissionByType($userDetail['role_id'], $userDetail['uid'], $type);

            if (!empty($getPermissionData)) {
                foreach ($getPermissionData as $key => $value) {
                    $data[count($data)] = $value['permission_id'];
                }
            }
        }
        return $data;
    }
}

if (!function_exists('getChildTabData')) {
    function getChildTabData($parentTabId)
    {
        $permissionListByType = permissionListByType('SIDEBAR');
        $getSideBarArray = TabList::getChildTabData($permissionListByType, $parentTabId);
        if ($getSideBarArray) {
            return $getSideBarArray;
        }
    }
}

if (!function_exists('generateSidebar')) {
    function generateSidebar()
    {
        $getSideBarArray = getTabDataByType('SIDEBAR');

        if ($getSideBarArray) {
            return generateSidebarData($getSideBarArray, 0);
        }
    }
}


if (!function_exists('generateSidebarData')) {
    function generateSidebarData($sidebarArray, $parentTabId)
    {


        $result = []; // Initialize the result array

        foreach ($sidebarArray as $key => $value) {

            $child = getChildTabData($value['tab_id']);
            $child = $child ?: [];

            if ($value['parent_id'] == 0) {
                $tabData = [
                    'tab_id' => $value['tab_id'],
                    'tab_name' => $value['tab_name'],
                    'tab_icon' => $value['tab_icon'],
                    'tab_class' => $value['tab_class'],
                    'tab_order' => $value['tab_order'],
                    'tab_url' => $value['tab_url'],
                ];

                if (!empty($child)) {
                    $childArray = [];
                    foreach ($child as $childTab) {

                        $childArray[] = [
                            'tab_id' => $childTab['tab_id'],
                            'tab_name' => $childTab['tab_name'],
                            'tab_icon' => $childTab['tab_icon'],
                            'tab_class' => $childTab['tab_class'],
                            'tab_order' => $childTab['tab_order'],
                            'tab_url' => $childTab['tab_url'],
                        ];
                    }

                    $tabData['child'] = $childArray;
                }

                $result[] = $tabData;
            }
        }

        return $result;
    }
}

if (!function_exists('printQuery')) {
    function printQuery($query, $bindings)
    {
        $pdo = DB::getPdo();
        foreach ($bindings as $binding) {
            $query = preg_replace('/\?/', $pdo->quote($binding), $query, 1);
        }
        echo $query;
        die;
    }
}


if (!function_exists('checkIfActionAllowed')) {
    function checkIfActionAllowed($permissionId, $permissionType, $uid = null)
    {
        $isAllowed = null;
        if (!empty($permissionId) && !empty($permissionType)) {

            $allowedType = [
                'FETCH' => 'can_read',
                'ADD' => 'can_write',
                'UPDATE' => 'can_update',
                'DELETE' => 'can_delete'
            ];

            if (in_array($permissionType, array_keys($allowedType))) {
                $valid = true;
                $userId = '';
                $roleId = '';


                // check if user id passed in function if not then check the logged in user uid
                if (!empty($uid)) {
                    $userDetail =  User::where('uid', $uid)->first();
                    if (!empty($userDetail)) {
                        $userId = $userDetail['uid'];
                        $roleId = $userDetail['role_id'];
                    } else {
                        $valid = false;
                    }
                } else {
                    $loggedInUser = getUserDataByToken();
                    if (!empty($loggedInUser)) {
                        $userId = $loggedInUser['uid'];
                        $roleId = $loggedInUser['role_id'];
                    }
                }

                if (!empty($userId) && !empty($roleId)) {

                    $getAllowedPermission = Permission::getAllowedPermissionById($permissionId, $userId, $roleId);

                    if (!empty($getAllowedPermission)) {

                        if ($getAllowedPermission[$allowedType[$permissionType]]) {
                            return true;
                        }
                    }
                }
            }
        }

        return $isAllowed;
    }
}

if (!function_exists('generatePsssword')) {
    function generatePsssword($name, $dob)
    {
        $password = '';
        $dob = str_replace('-', '', $dob);

        $password .= (strlen($name) >= 4) ? substr($name, 0, 4) : $name;
        $password .= substr($dob, 0, 4 - strlen($name));

        $password .= substr($dob, 0, 8 - strlen($password));
        if (!empty($password) && strlen($password) == 8) {
            return $password;
        }
    }
}
