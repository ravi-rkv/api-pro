<?php

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
