<?php

namespace Realtyna\MvcCore;

use Realtyna\MvcCore\Exception\LoggerNotInitializedException;

class Logger
{
    public static \Monolog\Logger $logger;

    /**
     * @throws LoggerNotInitializedException
     */
    public static function debug($log, array $context = [])
    {
        self::getLogger()->debug($log, $context);
    }

    /**
     * @throws LoggerNotInitializedException
     */
    public static function info($log, array $context = [])
    {
        self::getLogger()->info($log, $context);
    }

    /**
     * @throws LoggerNotInitializedException
     */
    public static function notice($log, array $context = [])
    {
        self::getLogger()->notice($log, $context);
    }

    /**
     * @throws LoggerNotInitializedException
     */
    public static function warning($log, array $context = [])
    {
        self::getLogger()->warning($log, $context);
    }

    /**
     * @throws LoggerNotInitializedException
     */
    public static function error($log, array $context = [])
    {
        self::getLogger()->error($log, $context);
    }

    /**
     * @throws LoggerNotInitializedException
     */
    public static function critical($log, array $context = [])
    {
        self::getLogger()->critical($log, $context);
    }

    /**
     * @throws LoggerNotInitializedException
     */
    public static function alert($log, array $context = [])
    {
        self::getLogger()->alert($log, $context);
    }


    /**
     * @throws LoggerNotInitializedException
     */
    public static function emergency($log, array $context = [])
    {
        self::getLogger()->emergency($log, $context);
    }


    /**
     * @return mixed
     */
    public static function getLogger()
    {
        if (!self::$logger) {
            throw new LoggerNotInitializedException();
        }
        return self::$logger;
    }

    /**
     * @param mixed $logger
     */
    public static function setLogger($logger): void
    {
        self::$logger = $logger;
    }
}