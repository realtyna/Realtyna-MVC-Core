<?php

namespace Unit;

use Realtyna\MvcCore\Config;
use Realtyna\MvcCore\Exception\ValidatorException;
use Realtyna\MvcCore\Validator;

class ValidatorTest extends \WP_UnitTestCase
{

    private Config $config;
    private Validator $validator;

    public function set_up()
    {
        parent::set_up();
        parent::set_up();
        $configsArray = [
            'path' => [
                'view' => __DIR__ . '/../..',
                'validator-messages' => __DIR__ . '/../../src/Fake/validation.php',
            ],
        ];

        $this->config = new Config($configsArray);
        $main = $this->getMockForAbstractClass('Realtyna\MvcCore\StartUp', [$this->config]);
        $this->validator = new Validator($main);
    }

    public function testLoadingMessages()
    {
        $this->assertEquals('Entered E-Mail Address is not valid.', $this->validator->messages['messages']['email']);
    }


    public function testRequestHandlerMethod()
    {
        $request = new \WP_REST_Request();
        $request->set_body_params([
            'testBody' => 'testBodyValue'
        ]);

        $request->set_query_params([
            'testQueryParam' => 'testQueryParamValue'
        ]);

        $parameters = [
            $request
        ];
        $reflection = new \ReflectionClass(Validator::class);
        $method = $reflection->getMethod('requestHandler');
        $method->setAccessible(true);
        $data = $method->invokeArgs($this->validator, $parameters);

        $this->assertEquals([
            'testBody' => 'testBodyValue',
            'testQueryParam' => 'testQueryParamValue'
        ], $data);
    }


    public function testGetErrorMessageWithNoArgs()
    {
        $parameters = [
            $input = 'email',
            $rule = 'required',
            $args = [],
        ];
        $reflection = new \ReflectionClass(Validator::class);
        $method = $reflection->getMethod('getErrorMessage');
        $method->setAccessible(true);
        $data = $method->invokeArgs($this->validator, $parameters);

        $this->assertEquals('E-Mail Address is required.', $data);
    }

    public function testGetErrorMessageWithArgs()
    {
        $parameters = [
            $input = 'email',
            $rule = 'min',
            $args = [8],
        ];
        $reflection = new \ReflectionClass(Validator::class);
        $method = $reflection->getMethod('getErrorMessage');
        $method->setAccessible(true);
        $data = $method->invokeArgs($this->validator, $parameters);

        $this->assertEquals('E-Mail Address should have more than 8 characters.', $data);
    }

    public function testValidateMethodWithWrongRuleName()
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Entered rule: test is not valid');
        $request = new \WP_REST_Request();
        $rules = [
            'email' => ['test']
        ];

        $this->validator->validate($request, $rules);
    }

    public function testValidateMethodWithOneRuleWithoutArgs()
    {
        $request = new \WP_REST_Request();
        $rules = [
            'email' => ['required']
        ];

        $validation = $this->validator->validate($request, $rules);
        $this->assertEquals([
            'valid' => false,
            'errors' => [
                'email' => [
                    'E-Mail Address is required.'
                ]
            ]
        ], $validation);
    }

    public function testValidateMethodWithOneRuleWithArgs()
    {
        $request = new \WP_REST_Request();
        $rules = [
            'email' => ['min:8']
        ];

        $validation = $this->validator->validate($request, $rules);
        $this->assertEquals([
            'valid' => false,
            'errors' => [
                'email' => [
                    'E-Mail Address should have more than 8 characters.'
                ]
            ]
        ], $validation);
    }

    public function testValidateMethodWithMultipleRule()
    {
        $request = new \WP_REST_Request();
        $rules = [
            'email' => ['required', 'min:8']
        ];

        $validation = $this->validator->validate($request, $rules);
        $this->assertEquals([
            'valid' => false,
            'errors' => [
                'email' => [
                    'E-Mail Address is required.',
                    'E-Mail Address should have more than 8 characters.'
                ]
            ]
        ], $validation);
    }
}