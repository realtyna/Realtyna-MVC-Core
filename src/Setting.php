<?php

namespace Realtyna\MvcCore;

abstract class Setting
{
    public StartUp $main;

    public function __construct(StartUp $main)
    {
        $this->main = $main;
        add_action('carbon_fields_register_fields', [$this, 'registerPluginOptions']);

    }
    abstract public function registerPluginOptions();
}