<?php

namespace Unit;

use Realtyna\MvcCore\Auth;
use Realtyna\MvcCore\Config;

class AuthTest extends \WP_UnitTestCase
{

    protected Config $config;
    protected $main;

    public function set_up()
    {
        parent::set_up();
        $configsArray = [
            'namespace' => 'test',
            'jwt_secret' => 'soMG4!P3C!Gjmj#Bf4',
        ];

        $this->config = new Config($configsArray);
        $this->main = $this->getMockForAbstractClass('Realtyna\MvcCore\StartUp', [$this->config]);
        $this->auth = new Auth($this->main);
    }

    public function testEncodeMethod()
    {
        $parameters = [
            12
        ];
        $reflection = new \ReflectionClass(Auth::class);
        $method = $reflection->getMethod('encode');
        $method->setAccessible(true);
        $encoded = $method->invokeArgs($this->auth, $parameters);

        $this->assertIsString($encoded);
    }

    public function testDecodeMethod()
    {
        $parameters = [
            12
        ];
        $reflection = new \ReflectionClass(Auth::class);
        $method = $reflection->getMethod('encode');
        $method->setAccessible(true);
        $encoded = $method->invokeArgs($this->auth, $parameters);


        $parameters = [
            $encoded
        ];
        $reflection = new \ReflectionClass(Auth::class);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);
        $decoded = $method->invokeArgs($this->auth, $parameters);

        $this->assertEquals(12, $decoded->user->userID);
    }

    public function testGenerateTokenMethod()
    {
        $token = $this->auth->generateToken(12);

        $parameters = [
            $token
        ];
        $reflection = new \ReflectionClass(Auth::class);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);
        $decoded = $method->invokeArgs($this->auth, $parameters);

        $this->assertEquals(12, $decoded->user->userID);
    }

    public function testGetUserMethodWithCorrectToken()
    {
        $userID = $this->factory()->user->create();
        $token = $this->auth->generateToken($userID);
        $user = $this->auth->getUser($token);
        $this->assertIsObject($user, \WP_User::class);
    }

    public function testGetUserMethodWithWrongToken()
    {
        $user = $this->auth->getUser('just a wrong token');
        $this->assertFalse($user);
    }
}