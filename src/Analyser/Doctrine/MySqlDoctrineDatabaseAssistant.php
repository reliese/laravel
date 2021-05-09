<?php

namespace Reliese\Analyser\Doctrine;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Connectors\ConnectionFactory;
use Reliese\Configuration\RelieseConfiguration;

/**
 * Class MySqlDoctrineDatabaseAssistant
 */
class MySqlDoctrineDatabaseAssistant implements DoctrineDatabaseAssistantInterface
{
    /**
     * @var RelieseConfiguration
     */
    private RelieseConfiguration $relieseConfiguration;

    /**
     * @var ConnectionInterface
     */
    private ConnectionInterface $configuredConnection;

    /**
     * @var ConnectionFactory
     */
    private ConnectionFactory $connectionFactory;

    /**
     * @var AbstractSchemaManager[]
     */
    private array $doctrineSchemaManagers = [];

    /**
     * @var array
     */
    private array $schemaConnections = [];

    public function __construct(
        RelieseConfiguration $relieseConfiguration,
        ConnectionFactory $connectionFactory,
        ConnectionInterface $configuredConnection
    ) {
        $this->connectionFactory = $connectionFactory;
        $this->configuredConnection = $configuredConnection;
        $this->schemaConnections[$configuredConnection->getConfig('database')] = $configuredConnection;
        $this->relieseConfiguration = $relieseConfiguration;
    }

    public function getSchemaNames(): array
    {
        return $this->getDoctrineSchemaManager()->listDatabases();
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

    /**
     * @return RelieseConfiguration
     */
    public function getRelieseConfiguration(): RelieseConfiguration
    {
        return $this->relieseConfiguration;
    }
}
