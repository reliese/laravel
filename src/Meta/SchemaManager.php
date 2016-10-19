<?php

/**
 * Created by Cristian.
 * Date: 02/10/16 07:37 PM.
 */
namespace Reliese\Meta;

use ArrayIterator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\MySqlConnection;
use IteratorAggregate;
use Reliese\Meta\MySql\Schema as MySqlSchema;
use RuntimeException;

class SchemaManager implements IteratorAggregate
{
    /**
     * @var array
     */
    protected static $lookup = [
        MySqlConnection::class => MySqlSchema::class,
    ];

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Reliese\Meta\Schema[]
     */
    protected $schemas = [];

    /**
     * SchemaManager constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->boot();
    }

    /**
     * Load all schemas from this connection.
     */
    public function boot()
    {
        if (! $this->hasMapping()) {
            throw new RuntimeException("There is no Schema Mapper registered for [{$this->type()}] connection.");
        }

        $schemas = forward_static_call([$this->getMapper(), 'schemas'], $this->connection);

        foreach ($schemas as $schema) {
            $this->make($schema);
        }
    }

    /**
     * @param string $schema
     *
     * @return \Reliese\Meta\Schema
     */
    public function make($schema)
    {
        if (array_key_exists($schema, $this->schemas)) {
            return $this->schemas[$schema];
        }

        return $this->schemas[$schema] = $this->makeMapper($schema);
    }

    /**
     * @param string $schema
     *
     * @return \Reliese\Meta\Schema
     */
    protected function makeMapper($schema)
    {
        $mapper = $this->getMapper();

        return new $mapper($schema, $this->connection);
    }

    /**
     * @return string
     */
    protected function getMapper()
    {
        return static::$lookup[$this->type()];
    }

    /**
     * @return string
     */
    protected function type()
    {
        return get_class($this->connection);
    }

    /**
     * @return bool
     */
    protected function hasMapping()
    {
        return array_key_exists($this->type(), static::$lookup);
    }

    /**
     * Register a new connection mapper.
     *
     * @param string $connection
     * @param string $mapper
     */
    public static function register($connection, $mapper)
    {
        static::$lookup[$connection] = $mapper;
    }

    /**
     * Get Iterator for schemas.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->schemas);
    }
}
