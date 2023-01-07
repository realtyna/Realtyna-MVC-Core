<?php

namespace Realtyna\MvcCore\Exception;

use Exception;

class ModelApiException extends Exception
{
    public function __construct(string $message = "", $data = null, int $code = 0, ?Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }
}