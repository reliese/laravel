<?php

namespace Reliese\Meta\Sqlite;

use Reliese\Meta\DatabaseInterface;

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
        return ['database'];
    }
}