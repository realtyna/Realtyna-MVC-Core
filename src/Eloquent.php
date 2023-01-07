<?php

namespace Realtyna\MvcCore;

use Illuminate\Database\Capsule\Manager;
use Singleton;

class Eloquent
{

    private static ?Eloquent $instance = null;
    public bool $success = true;

    private function __construct()
    {
        try {
            $capsule = new Manager;
            global $table_prefix;
            $capsule->addConnection([
                'driver' => 'mysql',
                'host' => DB_HOST,
                'database' => DB_NAME,
                'username' => DB_USER,
                'password' => DB_PASSWORD,
                'charset' => DB_CHARSET,
                'prefix' => $table_prefix,
            ]);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        } catch (\Throwable $e) {
            $this->success = false;
        }
    }

    public static function getInstance(): ?Eloquent
    {
        if (self::$instance == null) {
            self::$instance = new Eloquent();
        }

        return self::$instance;
    }

}