<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/5
 * Time: 23:35
 */

namespace apps\httpd\libraries;


class RandomStringFactory
{
    /**
     * @param $string
     * @param int $random_suffix_string_len
     * @return null|string
     */
    public function md5($string, $random_suffix_string_len = 0): ?string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $random_suffix_string_len; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return md5($string . $randomString);
    }
}