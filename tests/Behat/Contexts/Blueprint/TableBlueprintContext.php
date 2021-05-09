<?php

namespace Tests\Behat\Contexts\Blueprint;

use Behat\Behat\Tester\Exception\PendingException;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ForeignKeyBlueprint;
use Reliese\Blueprint\IndexBlueprint;
use Reliese\Blueprint\SchemaBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Tests\Test;

class TableBlueprintContext extends BlueprintContexts
{
    private ?TableBlueprint $lastTableBlueprint;

    /**
     * @param string $schemaName
     * @param string $tableName
     *
     * @return TableBlueprint
     */
    public function getTableBlueprint(string $schemaName, string $tableName): TableBlueprint
    {
        $schemaBlueprint = $this->getSchemaBlueprintContext()->getSchemaBlueprint($schemaName);

        Test::assertArrayHasKey(
            $tableName,
            $schemaBlueprint->getTableBlueprints()
            // TODO: Add meaningful message
        );

        return $schemaBlueprint->getTableBlueprint($tableName);
    }

    /**
     * @return TableBlueprint
     */
    public function getLastTableBlueprint(): TableBlueprint
    {
        Test::assertInstanceOf(
            TableBlueprint::class,
            $this->lastTableBlueprint
            // TODO: Add meaningful message
        );

        return $this->lastTableBlueprint;
    }

    /**
     * @Given /^SchemaBlueprint "([^"]*)" has TableBlueprint "([^"]*)"$/
     */
    public function addSchemaBlueprintTableBlueprint(string $schemaName, string $tableName)
    {
        $schemaBlueprint = $this->getSchemaBlueprintContext()->getSchemaBlueprint($schemaName);

        $tableBlueprint = new TableBlueprint($schemaBlueprint, $tableName);

        $schemaBlueprint->addTableBlueprint($tableBlueprint);

        $this->lastTableBlueprint = $tableBlueprint;
    }

    /**
     * @Given /^last TableBlueprint has identity ColumnBlueprint "([^"]*)"$/
     */
    public function lastTableHasIdentityColumnBlueprint($columnName)
    {
        $this->addIntIdentityColumnToTableBlueprint($this->getLastTableBlueprint(), $columnName);
    }

    /**
     * @Given /^Schema "([^"]*)" Table "([^"]*)" has int identity column "([^"]*)"$/
     *
     * @param $schemaName
     * @param $tableName
     * @param $columnName
     */
    public function addSchemaTableIntIdentityColumn(
        $schemaName,
        $tableName,
        $columnName
    ) {
        $schemaBlueprint = $this->getSchemaBlueprintContext()->getSchemaBlueprint($schemaName);
        $tableBlueprint = $schemaBlueprint->getTableBlueprint($tableName);
        $this->addIntIdentityColumnToTableBlueprint($tableBlueprint, $columnName);
    }

    public function addIntIdentityColumnToTableBlueprint(
        TableBlueprint $tableBlueprint,
        $columnName
    ) {
        $idColumn = new ColumnBlueprint(
            $tableBlueprint,
            $columnName,
            'int',
            false,
            -1,
            -1,
            -1,
            true,
            false
        );


        $primaryKey = new IndexBlueprint(
            $tableBlueprint,
            'primary_key',
            [$idColumn],
            true,
            false
        );

        $tableBlueprint->addColumnBlueprint($idColumn);
        $tableBlueprint->addIndexBlueprint($primaryKey);
    }

    /**
     * @Given /^last TableBlueprint has string ColumnBlueprint "([^"]*)" of length "([^"]*)"$/
     */
    public function lastTableHasStringColumnBlueprintOfLength($columnName, $length)
    {
        $columnOwner = $this->getLastTableBlueprint();

        $column = new ColumnBlueprint(
            $columnOwner,
            $columnName,
            'string',
            false,
            $length,
            -1,
            -1,
            false,
            false
        );

        $columnOwner->addColumnBlueprint($column);
    }

    /**
     * @Given /^last TableBlueprint has int ColumnBlueprint "([^"]*)"$/
     * @param string $columnName
     */
    public function lastTableHasIntColumn(string $columnName)
    {
        $this->addTableIntColumn($this->getLastTableBlueprint(), $columnName);
    }

    /**
     * @Given /^Schema "([^"]*)" Table "([^"]*)" Columns "([^"]*)" reference Schema "([^"]*)" Table "([^"]*)" Columns "([^"]*)" as foreign key name "([^"]*)"$/
     * @param string $referencingSchemaName
     * @param string $referencingTableName
     * @param string $referencingColumnNamesList
     * @param string $referencedSchemaName
     * @param string $referencedTableName
     * @param string $referencedColumnNamesList
     * @param string $foreignKeyName
     */
    public function lastTableHasForeignKey(
        string $referencingSchemaName,
        string $referencingTableName,
        string $referencingColumnNamesList,
        string $referencedSchemaName,
        string $referencedTableName,
        string $referencedColumnNamesList,
        string $foreignKeyName
    ) {
        $referencingColumnNames =  \array_map('trim', \explode(',', $referencingColumnNamesList));
        
        $referencedColumnNames = \array_map('trim', \explode(',', $referencedColumnNamesList));

        $referencedSchemaBlueprint = $this->getSchemaBlueprintContext()->getSchemaBlueprint($referencedSchemaName);
        $referencedTableBlueprint = $referencedSchemaBlueprint->getTableBlueprint($referencedTableName);
        $referencedColumnBlueprints = [];
        foreach ($referencedColumnNames as $columnName) {
            $referencedColumnBlueprints[$columnName] = $referencedTableBlueprint->getColumnBlueprint($columnName);
        }
        
        $referencingSchemaBlueprint = $this->getSchemaBlueprintContext()->getSchemaBlueprint($referencingSchemaName);
        $referencingTableBlueprint = $referencingSchemaBlueprint->getTableBlueprint($referencingTableName);
        $referencingColumnBlueprints = [];
        foreach ($referencingColumnNames as $columnName) {
            $referencingColumnBlueprints[$columnName] = $referencingTableBlueprint->getColumnBlueprint($columnName);
        }

        $foreignKeyBlueprint = new ForeignKeyBlueprint(
            $referencingTableBlueprint,
            $foreignKeyName,
            $referencingColumnBlueprints,
            $referencedTableBlueprint,
            $referencedColumnBlueprints
        );

        $referencingTableBlueprint->addForeignKeyBlueprint($foreignKeyBlueprint);
    }

    /**
     * @Given /^Schema "([^"]*)" Table "([^"]*)" has int column "([^"]*)"$/
     */
    public function addSchemaTableIntColumn($schemaName, $tableName, $columnName)
    {
        $schemaBlueprint = $this->getSchemaBlueprintContext()->getSchemaBlueprint($schemaName);
        $this->addTableIntColumn($schemaBlueprint->getTableBlueprint($tableName), $columnName);
    }

    public function addTableIntColumn(TableBlueprint $tableBlueprint, string $columnName)
    {
        $column = new ColumnBlueprint(
            $tableBlueprint,
            $columnName,
            'int',
            false,
            -1,
            -1,
            -1,
            true,
            false
        );

        $tableBlueprint->addColumnBlueprint($column);
    }

    public function addTableStringColumn(TableBlueprint $tableBlueprint, string $columnName, int $length)
    {
        $column = new ColumnBlueprint(
            $tableBlueprint,
            $columnName,
            'varchar',
            false,
            $length,
            -1,
            -1,
            false,
            false
        );

        $tableBlueprint->addColumnBlueprint($column);
    }

    /**
     * @Given /^Schema "([^"]*)" Table "([^"]*)" has string column "([^"]*)" of length "(\d+)"$/
     */
    public function schemaTableHasStringColumn($schemaName, $tableName, $columnName, int $length)
    {
        $schemaBlueprint = $this->getSchemaBlueprintContext()->getSchemaBlueprint($schemaName);
        $this->addTableStringColumn(
            $schemaBlueprint->getTableBlueprint($tableName),
            $columnName,
            $length
        );
    }
}
