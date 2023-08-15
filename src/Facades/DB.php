<?php

namespace Realtyna\MvcCore\Facades;

use Illuminate\Support\Facades\Facade;
use Realtyna\MvcCore\Database\Connection;

/**
 * @method static \Illuminate\Database\Query\Builder table(string $table, string $as = null)
 * @method static \Illuminate\Database\Query\Expression raw($value)
 * @method static array getQueryLog()
 * @method static array prepareBindings(array $bindings)
 * @method static array select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static bool insert(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static bool unprepared(string $query)
 * @method static int affectingStatement(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static int transactionLevel()
 * @method static int update(string $query, array $bindings = [])
 * @method static mixed selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static mixed transaction(\Closure $callback, int $attempts = 1)
 * @method static void beginTransaction()
 * @method static void commit()
 * @method static void rollBack(int $toLevel = null)
 *
 * @see \Illuminate\Database\DatabaseManager
 * @see \Illuminate\Database\Connection
 *
 * @author Alan B <alan.b@realtyna.net>
 */
class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return Connection|false
     */
    protected static function getFacadeAccessor()
    {
        return Connection::instance();
    }
}