<?php

namespace Realtyna\MvcCore\Logger\Processors;

use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Psr\Log\LogLevel;
class IntrospectionProcessor implements ProcessorInterface
{
    /** @var int */
    private $level;
    /** @var string[] */
    private $skipClassesPartials;
    /** @var int */
    private $skipStackFramesCount;
    /** @var string[] */
    private $skipFunctions = [
        'call_user_func',
        'call_user_func_array',
    ];

    /**
     * @param string|int $level The minimum logging level at which this Processor will be triggered
     * @param string[] $skipClassesPartials
     *
     * @phpstan-param Level|LevelName|LogLevel::* $level
     */
    public function __construct($level = Logger::DEBUG, array $skipClassesPartials = [], int $skipStackFramesCount = 0)
    {
        $this->level                = Logger::toMonologLevel($level);
        $this->skipClassesPartials  = array_merge(['Monolog\\'], $skipClassesPartials);
        $this->skipStackFramesCount = $skipStackFramesCount;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record): array
    {
        // return if the level is not high enough
        if ($record['level'] < $this->level) {
            return $record;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // skip first since it's always the current method
        array_shift($trace);
        // the call_user_func call is also skipped
        array_shift($trace);

        $i = 0;

        while ($this->isTraceClassOrSkippedFunction($trace, $i)) {
            if (isset($trace[$i]['class'])) {
                foreach ($this->skipClassesPartials as $part) {
                    if (strpos($trace[$i]['class'], $part) !== false) {
                        $i++;

                        continue 2;
                    }
                }
            } elseif (in_array($trace[$i]['function'], $this->skipFunctions)) {
                $i++;

                continue;
            }

            break;
        }

        $i += $this->skipStackFramesCount;

        // we should have the call source now
        $record['extra'] = array_merge(
            $record['extra'],
            [
                'file'     => $trace[$i]['file'] ?? null,
                'line'     => $trace[$i]['line'] ?? null,
                'class'    => $trace[$i + 1]['class'] ?? null,
                'callType' => $trace[$i + 1]['type'] ?? null,
                'function' => $trace[$i + 1]['function'] ?? null,
            ]
        );

        return $record;
    }

    /**
     * @param array[] $trace
     */
    private function isTraceClassOrSkippedFunction(array $trace, int $index): bool
    {
        if ( ! isset($trace[$index])) {
            return false;
        }

        return isset($trace[$index]['class']) || in_array($trace[$index]['function'], $this->skipFunctions);
    }
}
