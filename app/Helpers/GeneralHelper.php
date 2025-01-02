<?php

use App\Models\TempUserRegistration;

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

            $allowedUserRole = [
                '1' => 'ADM',
                '2' => 'USR',
            ];

            $uidSequence = '';
            if (in_array($userRoleId, array_keys($allowedUserRole))) {
                $getExistUid = \App\Models\User::select('uid')->where('role_id', $userRoleId)->orderBy('id', 'DESC')->first();

                if (!empty($getExistUid)) {

                    $lastUid = $getExistUid['uid'];
                    $checkInitials = substr($lastUid, 0, 3);

                    if (($checkInitials == $allowedUserRole[$userRoleId])) {
                        $uidSequence = substr($lastUid, 3);
                    } else {
                        return false;
                    }
                } else {
                    $uidSequence = '0';
                }
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

                $uid = $allowedUserRole[$userRoleId] . $numeric_part;

                $checkUidExist = \App\Models\User::where('uid', $uid)->first();

                if (!$checkUidExist) {
                    return $uid;
                }
            }
        }
    }
}
