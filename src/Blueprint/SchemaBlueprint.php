<?php

namespace Reliese\Blueprint;

use InvalidArgumentException;

/**
 * Class SchemaBlueprint
 */
class SchemaBlueprint
{
    /**
     * @var string
     */
    private string $connectionName;

    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    /**
     * @var string
     */
    private string $schemaName;

    /**
     * @var TableBlueprint[]
     */
    private array $tableBlueprints = [];

    /**
     * @var ViewBlueprint[]
     */
    private $viewBlueprints = [];

    /**
     * SchemaBlueprint constructor.
     *
     * @param DatabaseBlueprint $databaseBlueprint
     * @param string            $schemaName
     * @param string            $connectionName
     */
    public function __construct(DatabaseBlueprint $databaseBlueprint, string $schemaName, string $connectionName)
    {
        $this->schemaName = $schemaName;
        $this->databaseBlueprint = $databaseBlueprint;
        $this->connectionName = $connectionName;
    }

    /**
     * @param TableBlueprint $tableBlueprint
     */
    public function addTableBlueprint(TableBlueprint $tableBlueprint)
    {
        $this->tableBlueprints[$tableBlueprint->getName()] = $tableBlueprint;
    }

    /**
     * @param ViewBlueprint $viewBlueprint
     */
    public function addViewBlueprint(ViewBlueprint $viewBlueprint)
    {
        $this->viewBlueprints[$viewBlueprint->getName()] = $viewBlueprint;
    }

    /**
     * The DatabaseBlueprint that owns this SchemaBlueprint
     *
     * @return DatabaseBlueprint
     */
    public function getDatabaseBlueprint(): DatabaseBlueprint
    {
        return $this->databaseBlueprint;
    }

    /**
     * The name of this schema within the database
     *
     * @return string
     */
    public function getSchemaName(): string
    {
        return $this->schemaName;
    }

    /**
     * @param string $tableName
     *
     * @return TableBlueprint
     */
    public function getTableBlueprint(string $tableName) : TableBlueprint
    {
        if (!\array_key_exists($tableName, $this->tableBlueprints)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Unable to find a TableBlueprint in \"%s\" for table \"%s\"",
                    $this->getSchemaName(), $tableName
                )
            );
        }
        return $this->tableBlueprints[$tableName];
    }

    /**
     * @return TableBlueprint[]
     */
    public function getTableBlueprints(): array
    {
        return $this->tableBlueprints;
    }

    /**
     * Returns an array of strings identifying the tables that can be accessed through the current connection.
     *
     * @return string[]
     */
    public function getTableNames(): array
    {
        return array_keys($this->tableBlueprints);
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function hasTableBlueprint(string $tableName) : bool
    {
        return \array_key_exists($tableName, $this->tableBlueprints);
    }

    /**
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    /**
     * @param string $connectionName
     *
     * @return SchemaBlueprint
     */
    public function setConnectionName(string $connectionName): static
    {
        $this->connectionName = $connectionName;
        return $this;
    }
}
