<?php

namespace Reliese\Blueprint;

use Illuminate\Database\ConnectionInterface;
use Reliese\Meta\DatabaseInterface;

/**
 * Class DatabaseManager
 */
class DatabaseBlueprint
{
    /**
     * The name of the connection from the Laravel config/database.php file
     *
     * @var string
     */
    private $connectionName;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var SchemaBlueprint[]
     */
    private $schemaBlueprints = [];

    /**
     * @var DatabaseInterface
     */
    private $databaseAdapter;

    /**
     * DatabaseBlueprint constructor.
     * @param DatabaseInterface $databaseAdapter
     * @param string $connectionName
     * @param ConnectionInterface $connection
     */
    public function __construct(
        DatabaseInterface $databaseAdapter,
        string $connectionName,
        ConnectionInterface $connection
    ) {
        $this->connectionName = $connectionName;
        $this->connection = $connection;
        $this->databaseAdapter = $databaseAdapter;
    }

    /**
     * If a schema name is not provided, then the default schema for the connection will be used
     * @param string $schemaName
     * @return SchemaBlueprint
     */
    public function schema(string $schemaName): SchemaBlueprint
    {
        if (!empty($this->schemaBlueprints[$schemaName])) {
            return $this->schemaBlueprints[$schemaName];
        }

        return $this->schemaBlueprints[$schemaName] = new SchemaBlueprint(
            $this,
            $this->databaseAdapter->getSchema($schemaName),
            $schemaName
        );
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }
}