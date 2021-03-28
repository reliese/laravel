<?php

namespace Reliese\Blueprint;

/**
 * Class SchemaBlueprint
 */
class SchemaBlueprint
{
    /**
     * @var DatabaseBlueprint
     */
    private $databaseBlueprint;

    /**
     * @var string
     */
    private $schemaName;

    /**
     * @var TableBlueprint[]
     */
    private $tableBlueprints = [];

    /**
     * @var ViewBlueprint[]
     */
    private $viewBlueprints = [];

    /**
     * SchemaBlueprint constructor.
     *
     * @param DatabaseBlueprint $databaseBlueprint
     * @param string            $schemaName
     */
    public function __construct(DatabaseBlueprint $databaseBlueprint, string $schemaName)
    {
        $this->schemaName = $schemaName;
        $this->databaseBlueprint = $databaseBlueprint;
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
}