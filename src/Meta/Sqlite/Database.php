<?php

namespace Reliese\Meta\Sqlite;

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
        return ['database'];
    }
}