<?php

namespace Reliese\Blueprint;

use InvalidArgumentException;
use Reliese\Configuration\DatabaseBlueprintConfiguration;
use RuntimeException;

/**
 * Class DatabaseManager
 */
class DatabaseBlueprint
{
    /**
     * @var DatabaseBlueprintConfiguration
     */
    private DatabaseBlueprintConfiguration $databaseBlueprintConfiguration;

    /**
     * @var SchemaBlueprint[]
     */
    private array $schemaBlueprints = [];

    /**
     * DatabaseBlueprint constructor.
     *
     * @param DatabaseBlueprintConfiguration $databaseBlueprintConfiguration
     */
    public function __construct(DatabaseBlueprintConfiguration $databaseBlueprintConfiguration)
    {
        $this->databaseBlueprintConfiguration = $databaseBlueprintConfiguration;
    }

    public function applyBlueprintFilters(\Reliese\Configuration\DatabaseBlueprintConfiguration $getDatabaseBlueprintConfiguration)
    {
        throw new RuntimeException(__METHOD__.' is incomplete');
    }

    /**
     * @param SchemaBlueprint $schemaBlueprint
     */
    public function addSchemaBlueprint(SchemaBlueprint $schemaBlueprint)
    {
        $this->schemaBlueprints[$schemaBlueprint->getSchemaName()] = $schemaBlueprint;
    }

    /**
     * @param string $schemaName
     *
     * @return SchemaBlueprint
     */
    public function getSchemaBlueprint(string $schemaName): SchemaBlueprint
    {
        if (empty($this->getSchemaBlueprints()[$schemaName])) {
            throw new InvalidArgumentException("Unable to find SchemaBlueprint for \"$schemaName\"");
        }

        return $this->getSchemaBlueprints()[$schemaName];
    }

    /**
     * @return SchemaBlueprint[]
     */
    public function getSchemaBlueprints(): array
    {
        return $this->schemaBlueprints;
    }

    /**
     * Returns an array of strings identifying the schemas that can be accessed through the current connection.
     *
     * @return string[]
     */
    public function getSchemaNames(): array
    {
        return array_keys($this->getSchemaBlueprints());
    }

    /**
     * @return DatabaseBlueprintConfiguration
     */
    public function getDatabaseBlueprintConfiguration(): DatabaseBlueprintConfiguration
    {
        return $this->databaseBlueprintConfiguration;
    }
}
