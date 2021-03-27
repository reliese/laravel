<?php

namespace Reliese\Blueprint;

use Illuminate\Database\DatabaseManager;
use Reliese\Coders\Model\Config;
use Reliese\Meta\AdapterFactory;

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
     * @param AdapterFactory $adapterFactory
     * @param DatabaseManager $laravelDatabaseManager
     * @param Config $config
     */
    public function __construct(
        AdapterFactory $adapterFactory,
        DatabaseManager $laravelDatabaseManager,
        Config $config
    ) {
        $this->laravelDatabaseManager = $laravelDatabaseManager;
        $this->config = $config;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * @param $connectionName
     * @return DatabaseBlueprint
     */
    public function database($connectionName): DatabaseBlueprint
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