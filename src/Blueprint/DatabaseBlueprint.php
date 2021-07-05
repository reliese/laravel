<?php

namespace Reliese\Blueprint;

use Reliese\Meta\DatabaseInterface;
use RuntimeException;

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
     * @var \Illuminate\Database\Connection
     */
    private $connection;

    /**
     * @var SchemaBlueprint[]
     */
    private $schemaBlueprints;
    /**
     * @var DatabaseInterface
     */
    private $databaseAdapter;

    /**
     * DatabaseBlueprint constructor.
     * @param DatabaseInterface $databaseAdapter
     * @param string $connectionName
     * @param \Illuminate\Database\Connection $connection
     */
    public function __construct(
        $databaseAdapter,
        $connectionName,
        $connection
    ) {
        $this->connectionName = $connectionName;
        $this->connection = $connection;
        $this->databaseAdapter = $databaseAdapter;
    }

    /**
     * If a schema name is not provided, then the default schema for the connection will be used
     * @param string|null $schemaName
     * @return SchemaBlueprint
     */
    public function schema($schemaName)
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

    # region Accessors
    /**
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    # endregion Accessors
}