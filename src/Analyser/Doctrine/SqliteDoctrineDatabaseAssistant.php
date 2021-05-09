<?php

namespace Reliese\Analyser\Doctrine;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\ConnectionInterface;
use Reliese\Configuration\RelieseConfiguration;

/**
 * Class SqliteDoctrineDatabaseAssistant
 */
class SqliteDoctrineDatabaseAssistant implements DoctrineDatabaseAssistantInterface
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
     * @var AbstractSchemaManager[]
     */
    private array $doctrineSchemaManagers = [];

    /**
     * SqliteDoctrineDatabaseAssistant constructor.
     *
     * @param RelieseConfiguration $relieseConfiguration
     * @param ConnectionInterface $configuredConnection
     */
    public function __construct(
        RelieseConfiguration $relieseConfiguration,
        ConnectionInterface $configuredConnection
    ) {
        $this->relieseConfiguration = $relieseConfiguration;
        $this->configuredConnection = $configuredConnection;
    }

    public function getSchemaNames(): array
    {
        return ['default'];
    }

    /**
     * Returns connections configured for the specified schema
     *
     * @param string|null $schemaName Ignored because Sqlite only supports one schema
     *
     * @return ConnectionInterface
     */
    public function getConnection(?string $schemaName = null): ConnectionInterface
    {
        return $this->configuredConnection;
    }

    /**
     * @param string|null $schemaName Ignored because Sqlite only supports one schema
     *
     * @return AbstractSchemaManager
     */
    public function getDoctrineSchemaManager(?string $schemaName = null): AbstractSchemaManager
    {
        return $this->doctrineSchemaManagers['default'] ??= $this->getConnection()->getDoctrineSchemaManager();
    }

    /**
     * @return RelieseConfiguration
     */
    public function getRelieseConfiguration(): RelieseConfiguration
    {
        return $this->relieseConfiguration;
    }
}
