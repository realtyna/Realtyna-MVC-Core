<?php

namespace Realtyna\MvcCore;

abstract class Setting
{
    public StartUp $main;

    public function __construct(StartUp $main)
    {
        $this->main = $main;
    }

    abstract public function registerPluginOptions();
}