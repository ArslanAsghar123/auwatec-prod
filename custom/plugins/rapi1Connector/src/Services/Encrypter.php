<?php

declare(strict_types=1);

namespace Rapidmail\Shopware\Services;

class Encrypter
{
    private $key;
    private $cipher;
    private $iv;

    public function __construct(string $key, string $iv, string $cipher = 'AES256')
    {
        $this->key = $key;
        $this->cipher = $cipher;
        $this->iv = $iv;
    }

    public function encrypt(array $data): string
    {
        return base64_encode(openssl_encrypt(json_encode($data), $this->cipher, $this->key, 0, $this->iv));
    }
}