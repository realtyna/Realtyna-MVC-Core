<?php

namespace Unit;

use Realtyna\MvcCore\Config;
use Realtyna\MvcCore\Event;

class EventTest extends \WP_UnitTestCase
{
    public function set_up()
    {
        update_option('blogname', 'Test Blog Name');

        $configsArray = [
            'namespace' => 'realtyna-test',
            'path' => [
                'validator-messages' => __DIR__ . '/../../src/Fake/validation.php',
                'assets' => [
                    'css' => __DIR__ . '/../..',
                    'js' => __DIR__ . '/../..',
                ],
                'plugin_dir' => __DIR__ . '/../../',
                'views_dir' => __DIR__ . '/../../views',
            ]
        ];

        $this->config = new Config($configsArray);
        $this->main = $this->getMockForAbstractClass('Realtyna\MvcCore\StartUp', [$this->config]);
    }

    public function test_if_events_are_located()
    {
        $children  = array();
        foreach(get_declared_classes() as $class){
            echo $class .'<br>' . PHP_EOL;
            if($class instanceof Event) $children[] = $class;
        }
        
        echo "<pre>";
        var_dump($children);
        echo "</pre>";
        die();
    }
}