<?php

namespace Unit;

use Realtyna\MvcCore\Eloquent;
use Realtyna\MvcCore\Fake\FakePostModel;

class EloquentTest extends \WP_UnitTestCase
{

    /**
     * @var Eloquent|\Singleton|null
     */
    public $eloquent;

    public function set_up()
    {
        $this->eloquent = Eloquent::getInstance();
    }

    public function testIsSingleton()
    {
        $instance1 = Eloquent::getInstance();
        $instance2 = Eloquent::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testConnection()
    {
        $this->expectNotToPerformAssertions();
        FakePostModel::all();
    }
}