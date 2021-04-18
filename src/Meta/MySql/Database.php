<?php

namespace Reliese\Meta\MySql;

use Reliese\Meta\DatabaseInterface;

use function array_diff;

/**
 * Class Database
 */
class Database implements DatabaseInterface
{
    /**
     * @var Schema[]
     */
    private $schemaAdapters = [];

    /**
     * @var \Illuminate\Database\MySqlConnection
     */
    private $connection;

    /**
     * Database constructor.
     * @param \Illuminate\Database\MySqlConnection $connection
     */
    public function __construct(\Illuminate\Database\MySqlConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function getSchemaNames()
    {
        $schemas = $this->connection->getDoctrineSchemaManager()->listDatabases();

        return array_diff($schemas, [
            'information_schema',
            'sys',
            'mysql',
            'performance_schema',
        ]);
    }

    /**
     * @param string $schemaName
     * @return Schema
     */
    public function getSchema($schemaName)
    {
        if (!empty($this->schemaAdapters[$schemaName])) {
            return $this->schemaAdapters[$schemaName];
        }
        return $this->schemaAdapters[$schemaName] = new Schema($schemaName, $this->connection);
    }
}