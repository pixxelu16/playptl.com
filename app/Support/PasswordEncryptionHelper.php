<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

class PasswordEncryptionHelper
{
    /**
     * Generate or retrieve an RSA key pair for the current session.
     * We cache it in the session so it lasts the duration of page forms.
     */
    public static function getPublicKey(): string
    {
        if (Session::has('rsa_public_key') && Session::has('rsa_private_key')) {
            return Session::get('rsa_public_key');
        }

        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        if (!$res) {
            return '';
        }

        openssl_pkey_export($res, $privKey);
        $pubKeyDetails = openssl_pkey_get_details($res);
        $pubKey = $pubKeyDetails["key"];

        Session::put('rsa_private_key', $privKey);
        Session::put('rsa_public_key', $pubKey);

        return $pubKey;
    }

    /**
     * Decrypt data encrypted via RSA-OAEP / RSA on client side
     */
    public static function decrypt(string $ciphertext): string
    {
        $privateKeyPem = Session::get('rsa_private_key');
        if (!$privateKeyPem) {
            return $ciphertext; // Fallback
        }

        $data = base64_decode($ciphertext);
        if (!$data) {
            return $ciphertext;
        }

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if (!$privateKey) {
            return $ciphertext;
        }

        // Try decrypting. Try standard PKCS1 padding first, then OAEP.
        if (openssl_private_decrypt($data, $decrypted, $privateKey, OPENSSL_PKCS1_PADDING)) {
            return $decrypted;
        }
        if (openssl_private_decrypt($data, $decrypted, $privateKey, OPENSSL_PKCS1_OAEP_PADDING)) {
            return $decrypted;
        }

        return $ciphertext; // Return original if decryption fails
    }
}
