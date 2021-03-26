<?php

namespace Reliese\Meta\MySql;

use function array_diff;

/**
 * Class Database
 */
class Database
{
    /**
     * @var \Illuminate\Database\Connection
     */
    private $connection;

    public function __construct(\Illuminate\Database\Connection $connection)
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
}