<?php

namespace Realtyna\MvcCore;

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Realtyna\MvcCore\Exception\InvalidCallbackException;
use ReflectionMethod;
use DI;

use function Symfony\Component\Translation\t;

class StartUp
{

    /*
     * @inject
     */
    public Config $config;
    public array $actions = [];
    public array $filters = [];
    public array $components = [];
    public array $styles = [];
    public array $scripts = [];
    public array $localizeScripts = [];
    public array $apis = [];
    public array $settings = [];
    public array $notices = [];
    public View $view;
    public Validator $validator;
    public ?Eloquent $eloquent;
    public Phinx $phinx;
    public DI\Container $container;
    public License $license;


    public function init()
    {
    }

    public function components()
    {
    }

    public function onAdmin()
    {
    }

    public function api()
    {
    }

    public function activation()
    {
    }

    public function deactivation()
    {
    }

    public function uninstallation()
    {
    }

    public function onUpdate()
    {
    }

    public function settings()
    {
    }

    public function requirements(): bool
    {
        return true;
    }

    /**
     * @throws InvalidCallbackException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(Config $config)
    {

        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAnnotations(true);
        $containerBuilder->addDefinitions([
            Config::class => $config,
        ]);

        $container = $containerBuilder->build();
        $this->config = $container->get(Config::class);;
        $this->eloquent = Eloquent::getInstance();
        $this->container = $container;

        if ($this->requirements() && $this->coreRequirements()) {
            $this->init();
            if (is_admin()) {
                $this->onAdmin();
            }

            $this->addAction('after_setup_theme', [$this, 'loadCarbon']);
            $this->settings();
            $this->registerSettings();

            $this->api();
            $this->onUpdate();
            $this->registerAPIs();
            $this->components();
            $this->registerComponents();
            $this->registerAssets();
            $this->registerHooks();

        } else {
            $error = '<p><strong>' . $config->get('plugin.name') . '</strong> did not start. Check errors.</p>';
            $this->addNotice($error);
        }

        $this->registerNotices();
    }


    /**
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $accepted_args
     * @return array
     * @since 0.0.1
     */
    private function getHook(string $hook, $callback, int $priority = 10, int $accepted_args = 1): array
    {
        return [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];
    }


    /**
     * @param array $callback
     * @return void
     * @throws InvalidCallbackException
     * @since 0.0.1
     */
    private function validateCallback(array $callback)
    {
        if (count($callback) != 2) {
            throw new InvalidCallbackException('Callback array should have 2 item in it.');
        }

        if (!is_object($callback[0]) && !class_exists($callback[0])) {
            throw new InvalidCallbackException('Callback class does not exists.');
        }

        if (!method_exists($callback[0], $callback[1])) {
            throw new InvalidCallbackException('Callback method does not exists in defined class.');
        }

        $reflection = new ReflectionMethod($callback[0], $callback[1]);

        if (!$reflection->isPublic()) {
            throw new InvalidCallbackException("Called method is not public.");
        }
    }


    /**
     * @param string $hook
     * @param array $callback
     * @param int $priority
     * @param int $accepted_args
     * @return void
     * @throws InvalidCallbackException
     * @since 0.0.1
     */
    public function addAction(string $hook, array $callback, int $priority = 10, int $accepted_args = 1)
    {
        $this->validateCallback($callback);
        $this->actions[] = $this->getHook($hook, $callback, $priority, $accepted_args);
    }


    /**
     * @param string $hook
     * @param array $callback
     * @param int $priority
     * @param int $accepted_args
     * @return void
     * @throws InvalidCallbackException
     * @since 0.0.1
     */
    public function addFilter(string $hook, array $callback, int $priority = 10, int $accepted_args = 1)
    {
        $this->validateCallback($callback);
        $this->filters[] = $this->getHook($hook, $callback, $priority, $accepted_args);
    }

    /**
     * @param $component
     * @return void
     * @since 0.0.1
     */
    public function addComponent($component): void
    {
        $this->components [] = $component;
    }

    /**
     * add class to Settings
     * @param $class
     * @return void
     */
    public function addSetting($class)
    {
        $this->settings [] = $class;
    }

    /**
     * @param $handler
     * @param $path
     * @param array $dep
     * @param bool $isAdmin
     * @param bool $enqueue
     * @param $version
     * @return void
     * @since 0.0.1
     */
    public function addStyle(
        string $handler,
        string $path,
        array $dep = [],
        bool $isAdmin = false,
        bool $enqueue = true,
        string $version = null
    ) {
        $this->styles[] = [
            'handler' => $handler,
            'path' => $path,
            'dep' => $dep,
            'enqueue' => $enqueue,
            'is_admin' => $isAdmin,
            'version' => $version
        ];
    }

    /**
     * @param string $handler
     * @param string $path
     * @param array $dep
     * @param bool $isAdmin
     * @param bool $inFooter
     * @param bool $enqueue
     * @param string|null $version
     * @return void
     * @since 0.0.1
     */
    public function addScript(
        string $handler,
        string $path,
        array $dep = [],
        bool $isAdmin = false,
        bool $inFooter = false,
        bool $enqueue = true,
        string $version = null
    ) {
        $this->scripts[] = [
            'handler' => $handler,
            'path' => $path,
            'dep' => $dep,
            'enqueue' => $enqueue,
            'in_footer' => $inFooter,
            'is_admin' => $isAdmin,
            'version' => $version
        ];
    }

    /**
     * @param $handle
     * @param $objectName
     * @param $data
     * @return void
     * @since 0.0.1
     */
    public function addLocalizedScript($handle, $objectName, $data): void
    {
        $this->localizeScripts[$handle][] = [
            'object_name' => $objectName,
            'data' => $data,
        ];
    }

    /**
     * @param array $script
     * @return void
     * @since 0.0.1
     */
    private function enqueueScript(array $script)
    {
        wp_enqueue_script(
            $script['handler'],
            $this->config->get('path.assets.js') . '/' . $script['path'],
            $script['dep'],
            $script['version'],
            $script['in_footer']
        );
    }

    private function registerScript(array $script)
    {
        wp_register_script(
            $script['handler'],
            $this->config->get('path.assets.js') . '/' . $script['path'],
            $script['dep'],
            $script['version'],
            $script['in_footer']
        );
    }

    /**
     * @param $handle
     * @param array $script
     * @return void
     * @since 0.0.1
     */
    private function localizeScripts($handle, array $script)
    {
        wp_localize_script(
            $handle,
            $script['object_name'],
            $script['data']
        );
    }


    /**
     * @param array $style
     * @return void
     * @since 0.0.1
     */
    private function enqueueStyle(array $style)
    {
        wp_enqueue_style(
            $style['handler'],
            $this->config->get('path.assets.css') . '/' . $style['path'],
            $style['dep'],
            $style['version'],
        );
    }

    /**
     * @param array $style
     * @return void
     * @since 0.0.1
     */
    private function registerStyle(array $style)
    {
        wp_register_style(
            $style['handler'],
            $this->config->get('path.assets.css') . '/' . $style['path'],
            $style['dep'],
            $style['version'],
        );
    }

    /**
     * @return void
     * @since 0.0.1
     */
    public function registerAssets()
    {
        if (isset($this->scripts)) {
            foreach ($this->scripts as $script) {
                if ($script['is_admin']) {
                    add_action(
                        'admin_enqueue_scripts',
                        function () use ($script) {
                            if ($script['enqueue']) {
                                $this->enqueueScript($script);
                            } else {
                                $this->registerScript($script);
                            }
                            if (isset($this->localizeScripts[$script['handler']])) {
                                if ($this->localizeScripts[$script['handler']]) {
                                    foreach ($this->localizeScripts[$script['handler']] as $localizeScript) {
                                        $this->localizeScripts($script['handler'], $localizeScript);
                                    }
                                }
                            }
                        }
                    );
                } else {
                    add_action(
                        'wp_enqueue_scripts',
                        function () use ($script) {
                            if ($script['enqueue']) {
                                $this->enqueueScript($script);
                            } else {
                                $this->registerScript($script);
                            }
                            if (isset($this->localizeScripts[$script['handler']])) {
                                if ($this->localizeScripts[$script['handler']]) {
                                    foreach ($this->localizeScripts[$script['handler']] as $localizeScript) {
                                        $this->localizeScripts($script['handler'], $localizeScript);
                                    }
                                }
                            }
                        }
                    );
                }
            }
        }

        if (isset($this->styles)) {
            foreach ($this->styles as $style) {
                if ($style['is_admin']) {
                    add_action(
                        'admin_enqueue_scripts',
                        function () use ($style) {
                            if ($style['enqueue']) {
                                $this->enqueueStyle($style);
                            } else {
                                $this->registerStyle($style);
                            }
                        }
                    );
                } else {
                    add_action(
                        'wp_enqueue_scripts',
                        function () use ($style) {
                            if ($style['enqueue']) {
                                $this->enqueueStyle($style);
                            } else {
                                $this->registerStyle($style);
                            }
                        }
                    );
                }
            }
        }
    }

    /**
     * @param string $version
     * @param string $baseRoute
     * @param $class
     * @param array $callbacks
     * @return void
     * @since 0.0.1
     */
    public function addAPI(string $version, string $baseRoute, $class, array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $this->apis [$version][$baseRoute][$class][] = $callback;
        }
    }


    public function addNotice(string $message, string $type = 'error', bool $isDismissible = false)
    {
        $this->notices[] = [
            'type' => $type,
            'message' => $message,
            'isDismissible' => $isDismissible,
        ];
    }

    public function registerNotices()
    {
        foreach ($this->notices as $notice) {
            add_action('admin_notices', function () use ($notice) {
                $extraClass = '';
                $type = $notice['type'];
                $message = $notice['message'];
                if ($notice['isDismissible']) {
                    $extraClass .= 'is-dismissible';
                }
                $html = "
                    <div class=\"notice notice-$type $extraClass\">
                        $message
                    </div>
                    ";

                echo $html;
            });
        }
    }

    /**
     * @return void
     * @since 0.0.1
     */
    public function registerHooks(): void
    {
        if (isset($this->actions)) {
            foreach ($this->actions as $action) {
                add_action($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']);
            }
        }

        if (isset($this->filters)) {
            foreach ($this->filters as $filter) {
                add_filter($filter['hook'], $filter['callback'], $filter['priority'], $filter['accepted_args']);
            }
        }
    }

    /**
     * @return void
     * @throws InvalidCallbackException
     * @since 0.0.1
     */
    public function registerAPIs()
    {
        foreach ($this->apis as $version => $baseRoutes) {
            foreach ($baseRoutes as $baseRoute => $classes) {
                foreach ($classes as $class => $callbacks) {
                    foreach ($callbacks as $callback) {
                        $class = $this->container->make(is_object($class) ? get_class($class) : $class, [
                            'version' => $version,
                            'baseRoute' => $baseRoute
                        ]);
                        $this->addAction('rest_api_init', [$class, $callback]);
                    }
                }
            }
        }
    }

    /**
     * @return void
     * @since 0.0.1
     */
    public function registerComponents()
    {
        foreach ($this->components as $class) {
            $service = $this->container->get($class);

            if (method_exists($service, 'register')) {
                $service->register();
            }
        }
    }

    public function registerSettings()
    {
        foreach ($this->settings as $setting) {
            add_action('carbon_fields_register_fields', [$setting, 'registerPluginOptions']);
        }
    }

    /**
     * @return bool
     * @since 0.0.1
     */
    public function loadPluginTextDomain(): bool
    {
        return load_plugin_textdomain(
            $this->config->get('localize.textdomain'),
            false,
            $this->config->get('path.langs')
        );
    }

    /**
     * Loads Carbon fields
     * @return void
     */
    public function loadCarbon()
    {
        \Carbon_Fields\Carbon_Fields::boot();
    }

    /**
     * Generate hash based on domain name
     * @param $input
     * @param $length
     * @return false|string
     */
    private function generateUniqueHash($input, $length = 30)
    {
        // Create a raw binary sha256 hash and base64 encode it.
        $hash_base64 = base64_encode(hash('sha256', $input, true));
        // Replace non-urlsafe chars to make the string urlsafe.
        $hash_urlsafe = strtr($hash_base64, '+/', '-_');
        // Trim base64 padding characters from the end.
        $hash_urlsafe = rtrim($hash_urlsafe, '=');
        // Shorten the string before returning.
        return substr($hash_urlsafe, 0, $length);
    }


    private function coreRequirements(): bool
    {
        $valid = true;
        if (!extension_loaded('pdo_mysql')) {
            $html = '<p><strong>pdo_mysql</strong> extension is not installed. ask your host administrator to install it.</p>';
            $this->addNotice($html, 'error');
            $valid = false;
        }

        if (!defined('REALTYNA_JWT_SECRET')) {
            $html = '<p>
                    <strong>REALTYNA_JWT_SECRET</strong> is not defined in <strong>wp-config.php</strong>.
                    We will define a token for you but keep it in mind for better security you need 
                    to define it in <strong>wp-config.php</strong> like so:
<pre>
define("REALTYNA_JWT_SECRET", "YOUR RANDOM SECRET TOKEN")                    
</pre>
                    (Token can be anything, example: ' . bin2hex(random_bytes(18)) . ')
                </p>';
            $this->addNotice($html, 'error', true);
            define('REALTYNA_JWT_SECRET', $this->generateUniqueHash($_SERVER['SERVER_NAME'], 50));
        }

        return $valid;
    }

}