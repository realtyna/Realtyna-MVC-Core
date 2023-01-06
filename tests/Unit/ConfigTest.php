<?php

namespace Unit;

use Realtyna\MvcCore\Config;

class ConfigTest extends \WP_UnitTestCase
{
    public function testToLoadConfigs()
    {
        $configsArray = [
            'namespace' => 'realtyna-test',
            'path' => [
                'plugin_dir' => __DIR__,
                'views_dir' => __DIR__ . '/views',
            ]
        ];

        $config = new Config($configsArray);

        $this->assertEquals($config->getRaw(), $configsArray);
    }


    public function testToGetConfigs()
    {
        $configsArray = [
            'namespace' => 'realtyna-test',
            'path' => [
                'plugin_dir' => __DIR__,
                'views_dir' => __DIR__ . '/views',
            ],
            'assets' => [
                'css' => [
                    'client' => 'test.css'
                ]
            ]
        ];

        $config = new Config($configsArray);

        $this->assertEquals($config->get('namespace'), $configsArray['namespace']);
        $this->assertEquals($config->get('path.plugin_dir'), $configsArray['path']['plugin_dir']);
        $this->assertEquals($config->get('assets.css.client'), $configsArray['assets']['css']['client']);
    }

    public function testToGetConfigFromNewArray()
    {
        $configsArray = [
            'namespace' => 'realtyna-test',
        ];

        $config = new Config($configsArray);


        $customizedConfigsArray = [
            'namespace' => 'realtyna-customized-test',
        ];

        $this->assertEquals($config->get('namespace', $customizedConfigsArray), $customizedConfigsArray['namespace']);
    }
}