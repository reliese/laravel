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
            'postgres',
            'template0',
            'template1',
        ]);
    }
}