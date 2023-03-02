<?php

namespace Unit;

use ActionScheduler;
use Realtyna\MvcCore\Config;

class ActionSchedulerTest extends \WP_UnitTestCase
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

    public function test_action_scheduler_is_loaded()
    {
        $this->assertTrue(class_exists(\ActionScheduler::class));
    }

    public function test_if_single_action_is_scheduled_to_run_asap()
    {
        as_enqueue_async_action('test_hook', [1, 2, 3], 'test_group');
        $this->assertTrue(as_has_scheduled_action('test_hook'));
    }

    public function test_if_single_action_is_scheduled_to_run_at_specific_time()
    {
        $timestamp = strtotime('+1 minutes');
        $hook = 'test_hook_at_specific_time';
        $args = array('my_custom_arg' => 'custom_value');
        $group = '';

        as_schedule_single_action($timestamp, $hook, $args, $group);
        $this->assertTrue(as_has_scheduled_action('test_hook_at_specific_time'));
    }

    public function test_if_single_action_is_running_asap()
    {
        add_action('test_hook', [$this, 'changeTitle']);

        $title = 'Hello World!';
        as_enqueue_async_action('test_hook', [$title]);
        ActionScheduler::runner()->run();
        $this->assertEquals($title, get_option('blogname'));
    }


    public function changeTitle($title)
    {
        update_option('blogname', $title);
    }


}