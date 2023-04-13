<?php

namespace Realtyna\MvcCore;

class Container
{
    static public $container;
    public static function get($class){
        self::$container->get($class);
    }

    /**
     * @return mixed
     */
    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * @param mixed $container
     */
    public static function setContainer($container): void
    {
        self::$container = $container;
    }

}