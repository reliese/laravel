<?php

namespace Reliese\Blueprint;

use Reliese\Meta\Schema;
use Reliese\Meta\SchemaManager;

use function get_class;

/**
 * Class SchemaBlueprint
 */
class SchemaBlueprint
{
    /**
     * @var
     */
    private $schemaName;

    /**
     * @var
     */
    private $databaseBlueprint;

    /**
     * @var TableBlueprint[]
     */
    private $tableBlueprints;

    /**
     * @deprecated The SchemaBlueprint class should replace usage of SchemaManager. To maintain backwards compatibility,
     * SchemaBlueprint wraps SchemaManager
     *
     * @var SchemaManager
     */
    private $schemaManager;
    /**
     * @var Schema
     */
    private $schemaAdapter;

    /**
     * SchemaBlueprint constructor.
     * @param Schema $schemaAdapter
     * @param string $schemaName
     * @param DatabaseBlueprint $databaseBlueprint
     */
    public function __construct(
        $databaseBlueprint,
        Schema $schemaAdapter,
        $schemaName
    ) {
        $this->schemaAdapter = $schemaAdapter;
        $this->schemaName = $schemaName;
        $this->databaseBlueprint = $databaseBlueprint;

        $this->schemaManager = new SchemaManager(
            $databaseBlueprint->getConnection()
        );
    }

    public function table($tableName)
    {
        if (!empty($this->tableBlueprints[$tableName])) {
            return $this->tableBlueprints[$tableName];
        }

        $blueprint = $this->schemaAdapter->table($tableName);

        return $this->tableBlueprints[$tableName] = new TableBlueprint(
            $this,
            $tableName,
            $blueprint
        );
    }

    public function schemaAdapter()
    {

    }

    # region Accessors

    # endregion Accessors
}