<?php

namespace Realtyna\MvcCore;

class Controller
{

    public StartUp $main;

    public function __construct(StartUp $main)
    {
        $this->main = $main;
    }
}