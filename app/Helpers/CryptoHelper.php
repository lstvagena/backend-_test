<?php

namespace App\Helpers;

class CryptoHelper
{
  public static function decrypt($credential)
  {
    $decoded = base64_decode($credential);
    $vectorIVLength = openssl_cipher_iv_length("AES-256-CBC");
    $vectorIV = substr($decoded, 0, $vectorIVLength);
    $sha2len = 32;
    $decryptedRaw = substr($decoded, $vectorIVLength  + $sha2len);
    return openssl_decrypt($decryptedRaw, 'aes-256-cbc', config('app.encryption_key'), OPENSSL_RAW_DATA, $vectorIV);
  }
}
