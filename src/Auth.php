<?php

namespace Realtyna\MvcCore;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    public StartUp $main;
    public string $algorithm = 'HS256';


    /**
     * @param StartUp $main
     */
    public function __construct(StartUp $main)
    {
        $this->main = $main;
        $this->JWTSecretKey = $main->config->get('jwt_secret');
    }


    private function encode($userID, $expirationTimeInSecconds = 0): string
    {
        $issuedAt = time();
        if($expirationTimeInSecconds == 0){
            //default expiration time is 1 year
            $expirationTimeInSecconds = $issuedAt + (60 * 365) ;
        }
        $payload = [
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'exp' => $expirationTimeInSecconds,
            'user'  => [
                'userID' => $userID
            ]
        ];

        return JWT::encode($payload, $this->JWTSecretKey, $this->algorithm);
    }

    private function decode($token)
    {
        try{
            return JWT::decode($token, new Key($this->JWTSecretKey, $this->algorithm));
        }catch (Exception $e){
            return false;
        }
    }

    public function generateToken($userId){
        return $this->encode($userId);
    }

    public function getUser($token){
        try{
            $decodedData = $this->decode($token);
            if($decodedData){
                if($decodedData->user->userID != null){
                    return get_user_by('ID', $decodedData->user->userID);
                }
            }
            return false;
        }
        catch (Exception $e){
            return false;
        }
    }
}