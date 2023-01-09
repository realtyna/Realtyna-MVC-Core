<?php

namespace Realtyna\MvcCore\Fake;

class FakeComponent
{

    public function register()
    {
        throw new \Exception('Component was created.');
    }
}