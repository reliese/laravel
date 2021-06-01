<?php

namespace Reliese\Analyser\Doctrine\Adapters;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\ConnectionInterface;
use Reliese\Configuration\ConfigurationProfile;

/**
 * Class SqliteDoctrineDatabaseAssistant
 */
class SqliteDatabaseVendorAdapter implements DatabaseVendorAdapterInterface
{
    /**
     * @var ConfigurationProfile
     */
    private ConfigurationProfile $configurationProfile;

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
     * @param ConfigurationProfile $configurationProfile
     * @param ConnectionInterface $configuredConnection
     */
    public function __construct(
        ConfigurationProfile $configurationProfile,
        ConnectionInterface $configuredConnection
    ) {
        $this->configurationProfile = $configurationProfile;
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
}
