<?php

namespace Reliese\Blueprint;

use InvalidArgumentException;
/**
 * Class DatabaseManager
 */
class DatabaseBlueprint
{
    /**
     * @var DatabaseDescriptionDto
     */
    private $databaseDescriptionDto;

    /**
     * @var SchemaBlueprint[]
     */
    private $schemaBlueprints = [];

    /**
     * DatabaseBlueprint constructor.
     *
     * @param DatabaseDescriptionDto $databaseDescriptionDto
     */
    public function __construct(
        DatabaseDescriptionDto $databaseDescriptionDto
    ) {
        $this->databaseDescriptionDto = $databaseDescriptionDto;
    }

    /**
     * @return DatabaseDescriptionDto
     */
    public function getDatabaseDescriptionDto(): DatabaseDescriptionDto
    {
        return $this->databaseDescriptionDto;
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
}