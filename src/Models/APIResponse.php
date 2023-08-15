<?php

namespace Realtyna\MvcCore\Models;

class APIResponse
{
    public bool $success;
    public int $statusCode;
    public $data;


    public function __construct(bool $success, $data, $statusCode)
    {
        $this->success = $success;
        $this->data = $data;
        $this->statusCode = $statusCode;
    }
}