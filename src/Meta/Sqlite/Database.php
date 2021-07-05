<?php

namespace Reliese\Meta\Sqlite;

use Reliese\Meta\DatabaseInterface;

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
     * @var \Illuminate\Database\SQLiteConnection
     */
    private $connection;

    /**
     * Database constructor.
     * @param \Illuminate\Database\SQLiteConnection $connection
     */
    public function __construct(\Illuminate\Database\SQLiteConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function getSchemaNames()
    {
        return ['database'];
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