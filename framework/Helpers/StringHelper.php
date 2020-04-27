<?php

namespace Rid\Helpers;

/**
 * StringHelper类
 */
class StringHelper
{

    private static function my_simple_crypt(string $string, $action = 'e'): ?string
    {
        $secret_key = env('APP_SECRET_KEY');
        $secret_iv = env('APP_SECRET_IV');

        $encrypt_method = 'AES-256-CBC';
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $output = false;
        if ($action == 'e') {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } elseif ($action == 'd') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    public static function encrypt($data)
    {
        return self::my_simple_crypt(serialize($data), 'e');
    }

    public static function decrypt($data)
    {
        return unserialize(self::my_simple_crypt($data, 'd'));
    }
}
