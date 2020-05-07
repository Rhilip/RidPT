<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/7/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Libraries;

class Crypt
{
    protected string $key;
    protected string $iv;
    protected string $encrypt_method;

    public function __construct($secret_key, $secret_iv, $encrypt_method)
    {
        $this->key = hash('sha256', $secret_key);
        $this->iv = substr(hash('sha256', $secret_iv), 0, 16);
        $this->encrypt_method = $encrypt_method;
    }

    public function encrypt($data)
    {
        return base64_encode(openssl_encrypt($data, $this->encrypt_method, $this->key, 0, $this->iv));
    }

    public function decrypt($data)
    {
        return openssl_decrypt(base64_decode($data), $this->encrypt_method, $this->key, 0, $this->iv);
    }
}
