<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/11/2019
 * Time: 2019
 */

namespace Rid\Helpers;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JWTHelper
{
    public static function encode($payload, $key = null)
    {
        return JWT::encode($payload, $key ?? sha1(env('APP_SECRET_KEY')));
    }

    public static function decode(string $jwt, string $key = null, array $allowed_algs = array(), $allow_exp = false)
    {
        try {
            $payload = (array) JWT::decode($jwt, $key ?? sha1(env('APP_SECRET_KEY')), $allowed_algs ?: ['HS256']);   // jwt data in array
        } catch (Exception $e) {
            $payload = false;
            if ($allow_exp && $e instanceof ExpiredException) {
                // Since in this case , we can't get payload with jti information directly, we should manually decode jwt content
                list($headb64, $bodyb64, $cryptob64) = explode('.', $jwt);
                $payload = (array) JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
            }
        }

        return $payload;
    }
}
