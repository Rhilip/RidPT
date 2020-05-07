<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/7/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Libraries;

use Exception;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\ExpiredException;

class JWT
{
    protected string $key;
    protected array $allowed_algs;

    public function __construct($key, $allowed_algs)
    {
        $this->key = sha1($key);
        $this->allowed_algs = $allowed_algs;
    }

    public function encode($payload, $key = null): string
    {
        return FirebaseJWT::encode($payload, $key ?? $this->key);
    }

    public function decode(string $jwt, string $key = null, array $allowed_algs = [], $allow_exp = false)
    {
        try {
            $payload = (array)FirebaseJWT::decode($jwt, $key ?? $this->key, $allowed_algs ?: ['HS256']);   // jwt data in array
        } catch (Exception $e) {
            $payload = false;
            if ($allow_exp && $e instanceof ExpiredException) {
                // Since in this case , we can't get payload with jti information directly, we should manually decode jwt content
                list($headb64, $bodyb64, $cryptob64) = explode('.', $jwt);
                $payload = (array)FirebaseJWT::jsonDecode(FirebaseJWT::urlsafeB64Decode($bodyb64));
            }
        }

        return $payload;
    }
}
