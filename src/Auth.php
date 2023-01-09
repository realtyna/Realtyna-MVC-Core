<?php

namespace Realtyna\MvcCore;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use WP_User;

class Auth
{
    public StartUp $main;
    public string $algorithm = 'HS256';
    /**
     * @var mixed|null
     */
    private $JWTSecretKey;


    /**
     * @param StartUp $main
     * @since 0.0.1
     */
    public function __construct(StartUp $main)
    {
        $this->main = $main;
        $this->JWTSecretKey = $main->config->get('jwt_secret');
    }


    /**
     * @param int $userID
     * @param int $expirationTimeInSeconds
     * @return string
     * @since 0.0.1
     */
    private function encode(int $userID, int $expirationTimeInSeconds = 0): string
    {
        $issuedAt = time();
        if ($expirationTimeInSeconds == 0) {
            //default expiration time is 1 year
            $expirationTimeInSeconds = $issuedAt + (60 * 365);
        }
        $payload = [
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'exp' => $expirationTimeInSeconds,
            'user' => [
                'userID' => $userID
            ]
        ];

        return JWT::encode($payload, $this->JWTSecretKey, $this->algorithm);
    }

    /**
     * @param string $token
     * @return false|object|\stdClass
     * @since 0.0.1
     */
    private function decode(string $token)
    {
        try {
            return JWT::decode($token, new Key($this->JWTSecretKey, $this->algorithm));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param int $userId
     * @return string
     * @since 0.0.1
     */
    public function generateToken(int $userId): string
    {
        return $this->encode($userId);
    }

    /**
     * @param string $token
     * @return false|WP_User
     * @since 0.0.1
     */
    public function getUser(string $token)
    {
        try {
            $decodedData = $this->decode($token);
            if ($decodedData) {
                if ($decodedData->user->userID != null) {
                    return get_user_by('ID', $decodedData->user->userID);
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}