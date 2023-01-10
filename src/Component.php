<?php

namespace Realtyna\MvcCore;

abstract class Component
{

    public StartUp $main;

    abstract function register();

    public function __construct(StartUp $main)
    {
        $this->main = $main;
    }
}