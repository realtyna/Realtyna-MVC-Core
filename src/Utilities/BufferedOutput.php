<?php

namespace Realtyna\MvcCore\Utilities;

use Symfony\Component\Console\Output\Output;

class BufferedOutput extends Output
{
    protected string $buffer = '';

    public function doWrite($message, $newline)
    {
        $this->buffer .= $message. ($newline? PHP_EOL: '');
    }

    public function getBuffer()
    {
        return $this->buffer;
    }
}