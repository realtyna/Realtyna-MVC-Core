<?php

namespace Unit;

use Realtyna\MvcCore\Config;
use Realtyna\MvcCore\Exception\InvalidCallbackException;
use Realtyna\MvcCore\StartUp;
use Realtyna\MvcCore\Utilities\FakeAPICController;
use Realtyna\MvcCore\Utilities\FakeClass;
use Realtyna\MvcCore\Utilities\FakeComponent;
use Realtyna\MvcCore\Utilities\FakeController;

class StartUpTest extends \WP_UnitTestCase
{

    private Config $config;
    private StartUp $main;

    public function set_up()
    {
        $configsArray = [
            'namespace' => 'realtyna-test',
            'path' => [
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

    public function testStartUpConstructor()
    {
        $this->assertEquals($this->config, $this->main->config);
    }


    public function testGetHookMethodWith2Params()
    {
        $testCallback = function () {
            echo 'Sample callback!';
        };

        $parameters = [
            'hook_name',
            $testCallback
        ];

        $reflection = new \ReflectionClass(get_class($this->main));
        $method = $reflection->getMethod('getHook');
        $method->setAccessible(true);
        $hook = $method->invokeArgs($this->main, $parameters);


        $this->assertEquals([
            'hook' => 'hook_name',
            'callback' => $testCallback,
            'priority' => 10,
            'accepted_args' => 1,
        ], $hook);
    }

    public function testGetHookMethodWith3Params()
    {
        $testCallback = function () {
            echo 'Sample callback!';
        };

        $parameters = [
            'hook_name',
            $testCallback,
            25
        ];

        $reflection = new \ReflectionClass(get_class($this->main));
        $method = $reflection->getMethod('getHook');
        $method->setAccessible(true);
        $hook = $method->invokeArgs($this->main, $parameters);


        $this->assertEquals([
            'hook' => 'hook_name',
            'callback' => $testCallback,
            'priority' => 25,
            'accepted_args' => 1,
        ], $hook);
    }

    public function testGetHookMethodWith4Params()
    {
        $testCallback = function () {
            echo 'Sample callback!';
        };

        $parameters = [
            'hook_name',
            $testCallback,
            25,
            2
        ];

        $reflection = new \ReflectionClass(get_class($this->main));
        $method = $reflection->getMethod('getHook');
        $method->setAccessible(true);
        $hook = $method->invokeArgs($this->main, $parameters);


        $this->assertEquals([
            'hook' => 'hook_name',
            'callback' => $testCallback,
            'priority' => 25,
            'accepted_args' => 2,
        ], $hook);
    }

    public function testValidateCallbackMethodWhenCallbackArrayCountNot2()
    {
        $this->expectException(InvalidCallbackException::class);
        $this->expectExceptionMessage('Callback array should have 2 item in it.');

        $reflection = new \ReflectionClass(get_class($this->main));
        $method = $reflection->getMethod('validateCallback');
        $method->setAccessible(true);

        $method->invokeArgs($this->main, [['UserController']]);
    }

    public function testValidateCallbackMethodWhenClassNotExists()
    {
        $this->expectException(InvalidCallbackException::class);
        $this->expectExceptionMessage('Callback class does not exists');

        $reflection = new \ReflectionClass(get_class($this->main));
        $method = $reflection->getMethod('validateCallback');
        $method->setAccessible(true);

        $method->invokeArgs($this->main, [['SomeRandomClass', 'index']]);
    }

    public function testValidateCallbackMethodWhenMethodNotExists()
    {
        $this->expectException(InvalidCallbackException::class);
        $this->expectExceptionMessage('Callback method does not exists in defined class');

        $reflection = new \ReflectionClass(get_class($this->main));
        $method = $reflection->getMethod('validateCallback');
        $method->setAccessible(true);

        $method->invokeArgs($this->main, [[FakeClass::class, 'index']]);
    }

    public function testValidateCallbackMethodWhenMethodNotPublic()
    {
        $this->expectException(InvalidCallbackException::class);
        $this->expectExceptionMessage('Called method is not public.');


        $reflection = new \ReflectionClass(get_class($this->main));
        $method = $reflection->getMethod('validateCallback');
        $method->setAccessible(true);

        $method->invokeArgs($this->main, [[FakeClass::class, 'TestMethod']]);
    }

    public function testAddActionMethod()
    {
        $this->main->addAction(
            'hook_name',
            [FakeController::class, 'TestMethod'],
            25,
            2
        );

        $this->assertContains([
            'hook' => 'hook_name',
            'callback' => [FakeController::class, 'TestMethod'],
            'priority' => 25,
            'accepted_args' => 2,
        ], $this->main->actions);
    }


    public function testAddFilterMethod()
    {
        $this->main->addFilter(
            'hook_name',
            [FakeController::class, 'TestMethod'],
            25,
            2
        );

        $this->assertContains([
            'hook' => 'hook_name',
            'callback' => [FakeController::class, 'TestMethod'],
            'priority' => 25,
            'accepted_args' => 2,
        ], $this->main->filters);
    }

    public function testAddComponentMethod()
    {
        $this->main->addComponent(FakeComponent::class);
        $this->assertContains(FakeComponent::class, $this->main->components);
    }


    public function testAddStylesMethod()
    {
        $this->main->addStyle('realtyna-mvc-css', 'main.css', [], false, true, '1.00');

        $this->assertContains([
            'handler' => 'realtyna-mvc-css',
            'path' => 'main.css',
            'dep' => [],
            'enqueue' => true,
            'is_admin' => false,
            'version' => '1.00'
        ], $this->main->styles);
    }

    public function testAddScriptMethod()
    {
        $this->main->addScript('realtyna-mvc-js', 'main.js', [], false, true, true, '1.00');

        $this->assertContains([
            'handler' => 'realtyna-mvc-js',
            'path' => 'main.js',
            'dep' => [],
            'enqueue' => true,
            'in_footer' => true,
            'is_admin' => false,
            'version' => '1.00'
        ], $this->main->scripts);
    }


    public function testLocalizeScriptMethod()
    {
        $this->main->addLocalizedScript('realtyna-mvc-js', 'localize_test', [
            'test' => 'test',
            'version' => '1.0.0'
        ]);
        $this->assertContains([
            'object_name' => 'localize_test',
            'data' => [
                'test' => 'test',
                'version' => '1.0.0'
            ]
        ], $this->main->localizeScripts['realtyna-mvc-js']);
    }

    public function testRegisterAssetsMethodWithStyleEnqueuedOnClientSide()
    {
        $fileName = $this->createFile($this->main->config->get('path.assets.js') . "/test.css");

        $this->main->addStyle('realtyna-mvc-css', 'test.css', [], false, true, '1.00');
        $this->main->registerAssets();

        do_action('wp_enqueue_scripts');

        $this->assertTrue(wp_style_is('realtyna-mvc-css'));

        $this->unlink($fileName);
        wp_dequeue_style('realtyna-mvc-css');
    }

    public function testRegisterAssetsMethodWithStyleRegisteredOnClientSide()
    {
        $fileName = $this->createFile($this->main->config->get('path.assets.js') . "/test.css");

        $this->main->addStyle('realtyna-mvc-css', 'test.css', [], false, false, '1.00');
        $this->main->registerAssets();

        do_action('wp_enqueue_scripts');

        $this->assertTrue(wp_style_is('realtyna-mvc-css', 'registered'));

        $this->unlink($fileName);
        wp_dequeue_style('realtyna-mvc-css');
    }


    public function testRegisterAssetsMethodWithScriptEnqueuedOnClientSide()
    {
        $fileName = $this->createFile($this->main->config->get('path.assets.js') . "/test.js");

        $this->main->addScript('realtyna-mvc-js', 'test.js', [], false, true, true, '1.00');
        $this->main->registerAssets();

        do_action('wp_enqueue_scripts');

        $this->assertTrue(wp_script_is('realtyna-mvc-js'));

        $this->unlink($fileName);
        wp_dequeue_script('realtyna-mvc-js');
    }

    public function testRegisterAssetsMethodWithScriptRegisteredOnClientSide()
    {
        $fileName = $this->createFile($this->main->config->get('path.assets.js') . "/test.js");

        $this->main->addScript('realtyna-mvc-js', 'test.js', [], false, true, false, '1.00');
        $this->main->registerAssets();

        do_action('wp_enqueue_scripts');

        $this->assertTrue(wp_script_is('realtyna-mvc-js', 'registered'));

        $this->unlink($fileName);
        wp_dequeue_script('realtyna-mvc-js');
    }

    public function testRegisterAssetsMethodWithStyleEnqueuedOnAdminSide()
    {
        set_current_screen('admin');
        $fileName = $this->createFile($this->main->config->get('path.assets.js') . "/test.css");

        $this->main->addStyle('realtyna-mvc-css', 'test.css', [], true, true, '1.00');
        $this->main->registerAssets();

        do_action('admin_enqueue_scripts');

        $this->assertTrue(wp_style_is('realtyna-mvc-css'));

        $this->unlink($fileName);
        wp_dequeue_style('realtyna-mvc-css');
    }

    public function testRegisterAssetsMethodWithStyleRegisteredOnAdminSide()
    {
        set_current_screen('admin');
        $fileName = $this->createFile($this->main->config->get('path.assets.js') . "/test.css");

        $this->main->addStyle('realtyna-mvc-css', 'test.css', [], true, false, '1.00');
        $this->main->registerAssets();

        do_action('admin_enqueue_scripts');

        $this->assertTrue(wp_style_is('realtyna-mvc-css', 'registered'));

        $this->unlink($fileName);
        wp_dequeue_style('realtyna-mvc-css');
    }


    public function testRegisterAssetsMethodWithScriptEnqueuedOnAdminSide()
    {
        set_current_screen('admin');
        $fileName = $this->createFile($this->main->config->get('path.assets.js') . "/test.js");

        $this->main->addScript('realtyna-mvc-js', 'test.js', [], true, true, true, '1.00');
        $this->main->registerAssets();

        do_action('admin_enqueue_scripts');

        $this->assertTrue(wp_script_is('realtyna-mvc-js'));

        $this->unlink($fileName);
        wp_dequeue_script('realtyna-mvc-js');
    }

    public function testRegisterAssetsMethodWithScriptRegisteredOnAdminSide()
    {
        set_current_screen('admin');
        $fileName = $this->createFile($this->main->config->get('path.assets.js') . "/test.js");

        $this->main->addScript('realtyna-mvc-js', 'test.js', [], true, true, false, '1.00');
        $this->main->registerAssets();

        do_action('admin_enqueue_scripts');

        $this->assertTrue(wp_script_is('realtyna-mvc-js', 'registered'));

        $this->unlink($fileName);
        wp_dequeue_script('realtyna-mvc-js');
    }

    //TODO write test for localize scripts
    public function testAddAPIMethod()
    {
        $this->main->addAPI(
            'v4',
            'user',
            FakeClass::class,
            ['login', 'register'],
        );

        $this->assertContains('login', $this->main->apis['v4']['user'][FakeClass::class]);
        $this->assertContains('register', $this->main->apis['v4']['user'][FakeClass::class]);
    }

    public function testRegisterHooksMethodWithActions()
    {
        $this->main->addAction(
            'init',
            [FakeController::class, 'TestMethod'],
            25,
            2
        );
        $this->main->registerHooks();

        $this->assertTrue(has_action('init', [FakeController::class, 'TestMethod']) == 25);
    }

    public function testRegisterHooksMethodWithFilters()
    {
        $this->main->addFilter(
            'init',
            [FakeController::class, 'TestMethod'],
            25,
            2
        );
        $this->main->registerHooks();

        $this->assertTrue(has_filter('init', [FakeController::class, 'TestMethod']) == 25);
    }

    public function testRegisterAPIsMethod()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API class created with version:v4 and baseRoute:user');

        $this->main->addAPI(
            'v4',
            'user',
            FakeAPICController::class,
            ['register'],
        );

        $this->main->registerAPIs();
        $this->main->registerHooks();

        do_action('rest_api_init');
    }

    public function testRegisterComponentsMethod()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Component was created.');

        $this->main->addComponent(FakeComponent::class);
        $this->main->registerComponents();
    }

    private function createFile($filePath)
    {
        $file = fopen($filePath, "w");
        $txt = "just a test";
        fwrite($file, $txt);

        return $filePath;
    }

}
