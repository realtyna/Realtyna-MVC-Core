<?php

namespace Realtyna\MvcCore\Database;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;

use const DB_NAME;

/**
 * Connection WordPress database
 * @author Alan B <alan.b@realtyna.net>
 */
class Connection implements ConnectionInterface
{

    public $db;

    /**
     * Count of active transactions
     *
     * @var int
     */
    public int $transactionCount = 0;

    /**
     * The database connection configuration options.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Initializes the Database class
     *
     * @return Connection|false
     */
    public static function instance()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Set default connection wp
     */
    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
    }

    /**
     * Get a new raw query expression.
     *
     * @param mixed $value
     *
     * @return Expression
     */
    public function raw($value): Expression
    {
        return new Expression($value);
    }

    /**
     * Get a new query builder instance.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return new Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Run a select statement and return a single result.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return mixed
     * @throws QueryException
     *
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $query = $this->bind_params($query, $bindings);

        $result = $this->db->get_row($query);

        if ($result === false || $this->db->last_error) {
            throw new QueryException($query, $bindings, new \Exception($this->db->last_error));
        }

        return $result;
    }

    /**
     * Run a select statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return array
     * @throws QueryException
     *
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        $query = $this->bind_params($query, $bindings);

        $result = $this->db->get_results($query);

        if ($result === false || $this->db->last_error) {
            throw new QueryException($query, $bindings, new \Exception($this->db->last_error));
        }

        return $result;
    }

    /**
     * Run a select statement against the database and returns a generator.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return void
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        //
    }


    /**
     * A hacky way to emulate bind parameters into SQL query
     *
     * @param $query
     * @param $bindings
     * @param bool $update
     * @return mixed
     */
    private function bind_params($query, $bindings, $update = false)
    {
        $query = str_replace('"', '`', $query);
        $bindings = $this->prepareBindings($bindings);

        if (!$bindings) {
            return $query;
        }

        $bindings = array_map(function ($replace) {
            if (is_string($replace)) {
                $replace = "'" . esc_sql($replace) . "'";
            } elseif ($replace === null) {
                $replace = "null";
            }

            return $replace;
        }, $bindings);

        $query = str_replace(['%', '?'], ['%%', '%s'], $query);
        $query = vsprintf($query, $bindings);

        return $query;
    }

    /**
     * Bind and run the query
     *
     * @param string $query
     * @param array $bindings
     * @return array
     * @throws QueryException
     *
     */
    public function bind_and_run($query, $bindings = [])
    {
        $new_query = $this->bind_params($query, $bindings);

        $result = $this->db->query($new_query);

        if ($result === false || $this->db->last_error) {
            throw new QueryException($new_query, $bindings, new \Exception($this->db->last_error));
        }

        return (array)$result;
    }

    /**
     * Run an insert statement against the database.
     *
     * @param string $query
     * @param array $bindings
     *
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     *
     * @param string $query
     * @param array $bindings
     *
     * @return int
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param string $query
     * @param array $bindings
     *
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param array $bindings
     *
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        $new_query = $this->bind_params($query, $bindings, true);

        return $this->unprepared($new_query);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param string $query
     * @param array $bindings
     *
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        $new_query = $this->bind_params($query, $bindings, true);

        $result = $this->db->query($new_query);

        if ($result === false || $this->db->last_error) {
            throw new QueryException($new_query, $bindings, new \Exception($this->db->last_error));
        }

        return intval($result);
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param string $query
     *
     * @return bool
     */
    public function unprepared($query)
    {
        $result = $this->db->query($query);

        return ($result === false || $this->db->last_error);
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param array $bindings
     *
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // Micro-optimization: check for scalar values before instances
            if (is_bool($value)) {
                $bindings[$key] = intval($value);
            } elseif (is_scalar($value)) {
                continue;
            } elseif ($value instanceof \DateTime) {
                // We need to transform all instances of the DateTime class into an actual
                // date string. Each query grammar maintains its own date string format
                // so we'll just ask the grammar for the format to get from the date.
                $bindings[$key] = $value->format($grammar->getDateFormat());
            }
        }

        return $bindings;
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param \Closure $callback
     * @param int $attempts
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function transaction(\Closure $callback, $attempts = 1)
    {
        $this->beginTransaction();
        try {
            $data = $callback();
            $this->commit();
            return $data;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $transaction = $this->unprepared("START TRANSACTION;");
        if (false !== $transaction) {
            $this->transactionCount++;
        }
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if ($this->transactionCount < 1) {
            return;
        }
        $transaction = $this->unprepared("COMMIT;");
        if (false !== $transaction) {
            $this->transactionCount--;
        }
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        if ($this->transactionCount < 1) {
            return;
        }
        $transaction = $this->unprepared("ROLLBACK;");
        if (false !== $transaction) {
            $this->transactionCount--;
        }
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactionCount;
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function pretend(\Closure $callback)
    {
       //
    }

    public function getPostProcessor()
    {
        return new Processor();
    }

    public function getQueryGrammar()
    {
        $grammar = new Grammar();
        $grammar->setTablePrefix($this->getTablePrefix());
        return $grammar;
    }

    /**
     * Return self as PDO
     *
     * @return Connection
     */
    public function getPdo(): Connection
    {
        return $this;
    }

    /**
     * Return the last insert id
     *
     * @param  string $args
     *
     * @return int
     */
    public function lastInsertId($args)
    {
        return $this->db->insert_id;
    }

    /**
     * Get an option from the configuration options.
     *
     * @param string|null $option
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return Arr::get($this->config, $option);
    }

    public function getDatabaseName()
    {
        return DB_NAME;
    }

    public function table($table, $as = null)
    {
        $processor = $this->getPostProcessor();

        $query = new Builder($this, $this->getQueryGrammar(), $processor);

        return $query->from($table);
    }

    /**
     * Get the table prefix for the connection.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->db->prefix;
    }

    /**
     * Get the database connection name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getConfig('name');
    }

    /**
     * @return \wpdb
     */
    public function getQueryLog(): \wpdb
    {
        return $this->db;
    }
}
