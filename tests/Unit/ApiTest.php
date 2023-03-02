<?php

namespace Unit;

use Mpdf\Tag\Th;
use Realtyna\MvcCore\API;
use Realtyna\MvcCore\Config;

class ApiTest extends \WP_UnitTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Realtyna\MvcCore\StartUp|(\Realtyna\MvcCore\StartUp&\PHPUnit\Framework\MockObject\MockObject)
     */
    private $main;

    public function set_up()
    {
        parent::set_up();
        $configsArray = [
            'api' => [
                'namespace' => 'home-valuation'
            ],
            'path' => [
                'validator-messages' => __DIR__ . '/../../src/Fake/validation.php',
            ]
        ];

        $this->main = $this->getMockForAbstractClass('Realtyna\MvcCore\StartUp', [new Config($configsArray)]);
    }

    public function testConstructorMethod()
    {

        $API = new API($this->main, 'v4', 'user');

        $this->assertEquals('v4', $API->version);
        $this->assertEquals('user', $API->baseRoute);
        $this->assertEquals('realtyna/home-valuation', $API->namespace);
    }

}