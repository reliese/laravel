<?php

namespace Reliese\Analyser\Doctrine;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Reliese\Configuration\DatabaseAnalyserConfiguration;
use Reliese\Configuration\DatabaseBlueprintConfiguration;

/**
 * Class MySqlDoctrineDatabaseAssistant
 */
class MySqlDoctrineDatabaseAssistant implements DoctrineDatabaseAssistantInterface
{
    /**
     * @var ConnectionInterface
     */
    private ConnectionInterface $configuredConnection;

    /**
     * @var ConnectionFactory
     */
    private ConnectionFactory $connectionFactory;

    /**
     * @var DatabaseManager
     */
    private DatabaseManager $databaseManager;

    /**
     * @var AbstractSchemaManager[]
     */
    private array $doctrineSchemaManagers = [];

    /**
     * @var array
     */
    private $schemaConnections = [];

    public function __construct(
        DatabaseBlueprintConfiguration $databaseBlueprintConfiguration,
        DatabaseAnalyserConfiguration $databaseAnalyserConfiguration,
        DatabaseManager $databaseManager,
        ConnectionFactory $connectionFactory,
        ConnectionInterface $configuredConnection
    ) {
        $databaseBlueprintConfiguration->getSchemaFilter()
            ->excludeSchema(['information_schema'])
            ->excludeSchema(['mysql'])
            ->excludeSchema(['performance_schema'])
        ;

        $this->databaseManager = $databaseManager;
        $this->connectionFactory = $connectionFactory;
        $this->configuredConnection = $configuredConnection;
        $this->schemaConnections[$configuredConnection->getConfig('database')] = $configuredConnection;
    }

    public function getSchemaNames(): array
    {
        // TODO: Implement getSchemaNames() method.
        throw new \Exception(__METHOD__ . " has not been implemented.");
    }

    /**
     * Returns connections configured for the specified schema
     *
     * @param string|null $schemaName
     *
     * @return ConnectionInterface
     */
    public function getConnection(?string $schemaName = null): ConnectionInterface
    {
        if (empty($schemaName)) {
            return $this->configuredConnection;
        }

        if (!empty($this->schemaConnections[$schemaName])) {
            return $this->schemaConnections[$schemaName];
        }
        /*
         * Copy the configuration then overwrite the schema name
         */
        $config = $this->configuredConnection->getConfig();
        $config["database"] = $schemaName;

        return $this->schemaConnections[$schemaName] = $this->connectionFactory->make($config);
    }


    public function getDoctrineSchemaManager(?string $schemaName = null): AbstractSchemaManager
    {
        if (empty($schemaName)) {
            $schemaName = $this->configuredConnection->getConfig('database');
        }

        if (!empty($this->doctrineSchemaManagers[$schemaName])) {
            return $this->doctrineSchemaManagers[$schemaName];
        }

        $doctrineSchemaManager = $this->getConnection($schemaName)->getDoctrineSchemaManager();

        $platform = $doctrineSchemaManager->getDatabasePlatform();

        // add type mappings that are not defined in doctrine
        $platform->registerDoctrineTypeMapping('enum', 'string');

        return $this->doctrineSchemaManagers[$schemaName] = $doctrineSchemaManager;
    }
}
