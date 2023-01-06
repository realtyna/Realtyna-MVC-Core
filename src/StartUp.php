<?php

namespace Realtyna\MvcCore;

use Realtyna\MvcCore\Exception\InvalidCallbackException;
use ReflectionMethod;

abstract class StartUp
{

    public Config $config;
    public array $actions;
    public array $filters;
    public array $components;
    public array $styles;
    public array $scripts;
    public array $localizeScripts;

    public function __construct(Config $config)
    {
        $this->config = $config;
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
     */
    public function addFilter(string $hook, array $callback, int $priority = 10, int $accepted_args = 1)
    {
        $this->validateCallback($callback);
        $this->filters[] = $this->getHook($hook, $callback, $priority, $accepted_args);
    }

    /**
     * @param $component
     * @return void
     */
    public function addComponent($component): void
    {
        $this->components [] = $component;
    }

    /**
     * @param $handler
     * @param $path
     * @param array $dep
     * @param bool $isAdmin
     * @param bool $enqueue
     * @param $version
     * @return void
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
     */
    public function addLocalizedScript($handle, $objectName, $data): void
    {
        $this->localizeScripts[$handle][] = [
            'object_name' => $objectName,
            'data' => $data,
        ];
    }

    private function enqueueScript($script){
        wp_enqueue_script(
            $script['handler'],
            $this->config->get('path.assets.js') . '/' . $script['path'],
            $script['dep'],
            $script['version'],
            $script['in_footer']
        );
    }

    private function registerScript($script){
        wp_register_script(
            $script['handler'],
            $this->config->get('path.assets.js') . '/' . $script['path'],
            $script['dep'],
            $script['version'],
            $script['in_footer']
        );
    }

    private function localizeScripts($handle, $script){
        wp_localize_script(
            $handle,
            $script['object_name'],
            $script['data']
        );
    }


    private function enqueueStyle($style){
        wp_enqueue_style(
            $style['handler'],
            $this->config->get('path.assets.css') . '/' . $style['path'],
            $style['dep'],
            $style['version'],
        );
    }

    private function registerStyle($style){
        wp_register_style(
            $style['handler'],
            $this->config->get('path.assets.css') . '/' . $style['path'],
            $style['dep'],
            $style['version'],
        );
    }

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
                            if (isset($this->localizeScripts)) {
                                if ($this->localizeScripts[$script['handler']]) {
                                    foreach ($this->localizeScripts[$script['handler']] as $localizeScript){
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
                            if (isset($this->localizeScripts)) {
                                if ($this->localizeScripts[$script['handler']]) {
                                    foreach ($this->localizeScripts[$script['handler']] as $localizeScript){
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
}