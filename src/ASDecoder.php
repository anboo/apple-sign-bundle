<?php

namespace Anboo\AppleSign;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Symfony\Component\DependencyInjection\Container;

class ASDecoder
{
    /**
     * Decode the Apple encoded JWT using Apple's public key for the signing.
     *
     * @throws NotFoundPublicKeyWithKidException
     * @param string $identityToken
     * @return Payload
     */
    public function decodeIdentityToken(string $identityToken) : Payload
    {
        list($jwtHeader,,) = explode('.', $identityToken);
        $jwtHeader = json_decode(base64_decode($jwtHeader), true);

        $publicKeyData = self::fetchPublicKey($jwtHeader['kid']);

        $publicKey = $publicKeyData['publicKey'];
        $alg = $publicKeyData['alg'];

        $payload = JWT::decode($identityToken, $publicKey, [$alg]);

        $result = new Payload();

        foreach ($payload as $field => $value) {
            $field = lcfirst(Container::camelize($field));
            if (property_exists($result, $field)) {
                $result->{$field} = $field == 'emailVerified' ? (bool)$value: $value;
            }
        }

        return $result;
    }

    /**
     * Fetch Apple's public key from the auth/keys REST API to use to decode
     * the Sign In JWT.
     *
     * @throws NotFoundPublicKeyWithKidException
     * @return array
     */
    public function fetchPublicKey(string $kid) : array
    {
        $publicKeys = file_get_contents('https://appleid.apple.com/auth/keys');
        $decodedPublicKeys = json_decode($publicKeys, true);

        if (!isset($decodedPublicKeys['keys']) || count($decodedPublicKeys['keys']) < 1) {
            throw new \RuntimeException('Invalid key format.');
        }

        $parsedKeyData = null;
        foreach ($decodedPublicKeys['keys'] as $key) {
            if ($key['kid'] == $kid) {
                $parsedKeyData = $key;
                break;
            }
        }

        if (!$parsedKeyData) {
            throw new NotFoundPublicKeyWithKidException(
                'Not found public key in https://appleid.apple.com/auth/keys with kid: '.$kid
            );
        }

        $parsedPublicKey= JWK::parseKey($parsedKeyData);
        $publicKeyDetails = openssl_pkey_get_details($parsedPublicKey);

        if (!isset($publicKeyDetails['key'])) {
            throw new \RuntimeException('Invalid public key details.');
        }

        return [
            'publicKey' => $publicKeyDetails['key'],
            'alg' => $parsedKeyData['alg']
        ];
    }
}
