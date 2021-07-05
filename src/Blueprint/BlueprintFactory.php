<?php

namespace Reliese\Blueprint;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Reliese\Coders\Model\Config;

use Reliese\Meta\AdapterFactory;
use Reliese\Meta\DatabaseInterface;
use Reliese\Meta\MySql\Database as MySqlDatabase;
use Reliese\Meta\Postgres\Database as PostgresDatabase;
use Reliese\Meta\Sqlite\Database as SqliteDatabase;

use function get_class;

/**
 * Class DatabaseFactory
 */
class BlueprintFactory
{
    /**
     * @var DatabaseBlueprint
     */
    private $laravelDatabaseManager;

    /**
     * @var DatabaseBlueprint[]
     */
    private $databaseBlueprints = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * BlueprintFactory constructor.
     * @param DatabaseManager $databaseManager
     * @param AdapterFactory $adapterFactory
     * @param Config $config
     */
    public function __construct(
        $adapterFactory,
        $databaseManager,
        $config
    ) {
        $this->laravelDatabaseManager = $databaseManager;
        $this->config = $config;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * @param $connectionName
     * @return DatabaseBlueprint
     */
    public function database($connectionName)
    {
        if (!empty($this->databaseBlueprints[$connectionName])) {
            return $this->databaseBlueprints[$connectionName];
        }

        $connection = $this->laravelDatabaseManager->connection($connectionName);

        $databaseBlueprint = new DatabaseBlueprint(
            $this->adapterFactory->database($connection),
            $connectionName,
            $connection
        );

        return $this->databaseBlueprints[$connectionName] = $databaseBlueprint;
    }
}