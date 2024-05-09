<?php

namespace Reliese\Meta\Postgres;

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
     * @var \Illuminate\Database\PostgresConnection
     */
    private $connection;

    /**
     * Database constructor.
     * @param \Illuminate\Database\PostgresConnection $connection
     */
    public function __construct(\Illuminate\Database\PostgresConnection $connection)
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
            'postgres',
            'template0',
            'template1',
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