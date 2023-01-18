<?php

namespace Unit;

use Realtyna\MvcCore\Auth;
use Realtyna\MvcCore\Config;
use Realtyna\MvcCore\View;

class ViewTest extends \WP_UnitTestCase
{

    public function set_up()
    {
        parent::set_up();
        $configsArray = [
            'path' => [
                'view' => __DIR__ . '/../../templates',
                'validator-messages' => __DIR__ . '/../../src/Fake/validation.php',
            ],
        ];

        $this->config = new Config($configsArray);
        $this->main = $this->getMockForAbstractClass('Realtyna\MvcCore\StartUp', [$this->config]);
        $this->view = new View($this->main);
    }

    public function testGetMethodNoTemplateExists()
    {
        $view = $this->view->get('admin/index');
        $this->assertFalse($view);
    }


    public function testGetMethodTemplateExistsReturnTemplate()
    {
        $file = fopen(__DIR__ . '/../../templates/user.php', "w");
        $txt = "<h1>Hello World!</h1>";
        fwrite($file, $txt);
        ob_start();
        $this->view->get('user');
        $content = ob_get_contents();
        ob_end_clean();
        $this->assertStringContainsString("Hello World!", $content);
        unlink(__DIR__ . '/../../templates/user.php');
    }

    public function testGetMethodTemplateExistsReturnLocation()
    {
        $file = fopen(__DIR__ . '/../../templates/user.php', "w");
        $txt = "<h1>Hello World!</h1>";

        fwrite($file, $txt);
        $path = $this->view->get('user', null, false);
        $this->assertEquals(__DIR__ . '/../../templates/user.php', $path);
        unlink(__DIR__ . '/../../templates/user.php');
    }
}