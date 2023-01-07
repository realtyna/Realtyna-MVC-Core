<?php

namespace Realtyna\MvcCore\Fake;

use Exception;

class FakeAPICController
{


    protected string $version;
    protected string $baseRoute;

    public function __construct(string $version, string $baseRoute)
    {
        $this->version = $version;
        $this->baseRoute = $baseRoute;
    }

    public function register()
    {
        throw new Exception("API class created with version:$this->version and baseRoute:$this->baseRoute");

    }
}