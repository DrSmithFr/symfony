<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;

class EncryptionService
{
    public function __construct(private readonly string $publicKey, private readonly string $secretKey)
    {
    }

    public function encryptData(string $cipher): string
    {
        if (!openssl_public_encrypt($cipher, $encrypted, $this->getPublicKey())) {
            throw new RuntimeException('Failed to encrypt data');
        }

        return $encrypted;
    }

    public function decryptData(string $encrypted): string
    {
        openssl_private_decrypt($encrypted, $cipher, $this->getPrivateKey());
        return $cipher;
    }

    private function getPrivateKey(): string
    {
        return trim(file_get_contents($this->secretKey));
    }

    private function getPublicKey(): string
    {
        return trim(file_get_contents($this->publicKey));
    }
}
