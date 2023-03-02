<?php

namespace Unit;

use Illuminate\Database\Eloquent\Collection;
use PhpParser\Builder\Class_;
use Realtyna\MvcCore\Config;
use Realtyna\MvcCore\Eloquent;
use Realtyna\MvcCore\Exception\ModelApiException;
use Realtyna\MvcCore\Model;
use Realtyna\MvcCore\Fake\FakePostModel;
use Realtyna\MvcCore\StartUp;
use WP_REST_Server;

class ModelTest extends \WP_UnitTestCase
{
    private Config $config;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Realtyna\MvcCore\StartUp|(\Realtyna\MvcCore\StartUp&\PHPUnit\Framework\MockObject\MockObject)
     */
    private $main;

    public function set_up()
    {
//        Eloquent::getInstance();


        $configsArray = [
            'namespace' => 'test',
            'path' => [
                'validator-messages' => __DIR__ . '/../../src/Fake/validation.php',
            ]
        ];

        $this->config = new Config($configsArray);
        $this->main = new StartUp($this->config);

        $apiController = new class($this->main, 'v4', 'user') {

            public function __construct(StartUp $main, string $version, string $baseRoute)
            {
                $this->version = $version;
                $this->baseRoute = $baseRoute;
            }

            public function register()
            {
                register_rest_route('test/' . $this->version, $this->baseRoute . '/register', array(
                    'methods' => [WP_REST_Server::READABLE],
                    'callback' => [$this, 'registerRouteCallback'],
                    'permission_callback' => '__return_true'
                ));
            }

            public function registerRouteCallback(\WP_REST_Request $requset)
            {
                return $requset->get_query_params();
            }

            public function login()
            {
                register_rest_route('test/' . $this->version, $this->baseRoute . '/login', array(
                    'methods' => [WP_REST_Server::CREATABLE],
                    'callback' => [$this, 'loginRouteCallback'],
                    'permission_callback' => '__return_true'
                ));
            }

            public function loginRouteCallback(\WP_REST_Request $requset)
            {
                return $requset->get_body_params();
            }

            public function forget()
            {
                register_rest_route('test/' . $this->version, $this->baseRoute . '/forget', array(
                    'methods' => [WP_REST_Server::CREATABLE],
                    'callback' => [$this, 'forgetRouteCallback'],
                    'permission_callback' => '__return_true'
                ));
            }

            public function forgetRouteCallback(\WP_REST_Request $requset)
            {
                return $requset->get_headers();
            }
        };

        $this->main->addAPI('v4', 'user', get_class($apiController), [
            'register',
            'login',
            'forget',
        ]);
        $this->main->registerAPIs();
        $this->main->registerHooks();
        global $wp_rest_server;
        $this->server = $wp_rest_server = new WP_REST_Server();
        do_action('rest_api_init');
    }

//    public function testDBConnection()
//    {
//        $this->factory()->post->create();
//
//        $fakePosts = FakePostModel::all();
//        $this->assertInstanceOf(Collection::class, $fakePosts);
//
//        $fakePosts = FakePostModel::where('id', 112)->get();
//        $this->assertInstanceOf(Collection::class, $fakePosts);
//    }

    public function testAPIMethod()
    {
        $model = Model::api('wp/v2');

        $this->assertEquals('wp/v2', $model::$apiRoute);
        $model::$apiRoute = null;
    }

    public function testMethodWithGet()
    {
        $model = Model::api('wp/v2')->method('get');

        $this->assertEquals('GET', $model::$method);
        $model::$apiRoute = null;
        $model::$method = null;
    }

    public function testMethodWithPost()
    {
        $model = Model::api('wp/v2')->method('post');

        $this->assertEquals('POST', $model::$method);
        $model::$apiRoute = null;
        $model::$method = null;
    }


    public function testMethodWithPut()
    {
        $model = Model::api('wp/v2')->method('put');

        $this->assertEquals('PUT', $model::$method);
        $model::$apiRoute = null;
        $model::$method = null;
    }


    public function testMethodWithPatch()
    {
        $model = Model::api('wp/v2')->method('patch');

        $this->assertEquals('PATCH', $model::$method);
        $model::$apiRoute = null;
        $model::$method = null;
    }

    public function testMethodWithDelete()
    {
        $model = Model::api('wp/v2')->method('delete');

        $this->assertEquals('DELETE', $model::$method);
        $model::$apiRoute = null;
        $model::$method = null;
    }

    public function testMethodWithWrongMethod()
    {
        $this->expectException(ModelApiException::class);
        $this->expectExceptionMessage('Entered method is not valid.');
        $model = Model::api('wp/v2')->method('test');

        $this->assertEquals('GET', $model::$method);
        $model::$apiRoute = null;
        $model::$method = null;
    }


    public function testWhereMethod()
    {
        $model = Model::api('wp/v2')->where('id', 1);

        $this->assertEquals(1, ($model::$conditions)['id']);

        $model::$apiRoute = null;
        $model::$conditions = null;
        $model::$method = null;
    }

    public function testHeaderMethod()
    {
        $model = Model::api('wp/v2/posts')->header(['Auth: bearer 121234']);

        $this->assertContains('Auth: bearer 121234', $model::$headers);
        $model::$headers = [];
    }

    public function testSendMethod()
    {
        $model = Model::api('/wp/v2/posts')->method('get')->send(true);

        $this->assertIsArray($model);
    }


    public function testSendGetMethodWithQueryParams()
    {
        $response = Model::api('/test/v4/user/register')->method('get')->where('id', 223)->send(true);
        $this->assertEquals(['id' => 223], $response);
    }

    public function test_send_post_method_with_body_params()
    {
        $response = Model::api('/test/v4/user/login')->method('post')->where('id', 223)->send(true);
        $this->assertEquals(['id' => 223], $response);
    }

    public function testSendMethodWithHeader()
    {
        $response = Model::api('/test/v4/user/forget')->method('post')->header(['id: hello', 'name:Alex'])->send(true);
        $this->assertContains(['id: hello'], $response);
        $this->assertContains(['name:Alex'], $response);
    }


    public function testToObjectMethodWhenMultipleObjectIsReturned()
    {
        $model = new class() extends Model {
            protected $guarded = [];
        };
        $response = $model::api("/wp/v2/posts")->method('get')->send()->toObject();

        $this->assertIsObject($response, Collection::class);
    }

    public function testToObjectMethodWhenSingleObjectIsReturned()
    {
        //TODO
        $this->assertTrue(true);
    }


}