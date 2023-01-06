<?php

namespace Realtyna\MvcCore\Utilities;

class FakeComponent
{

    public function register()
    {
        throw new \Exception('Component was created.');
    }
}