<?php

namespace Tests\Behat\Contexts;

use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\IndexBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Tests\Test;

class TableBlueprintContext extends FeatureContext
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
        $schemaBlueprint = $this->schemaBlueprintContext->getSchemaBlueprint($schemaName);

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
    public function givenSchemaBlueprintHasTableBlueprint(string $schemaName, string $tableName)
    {
        $schemaBlueprint = $this->schemaBlueprintContext->getSchemaBlueprint($schemaName);

        $tableBlueprint = new TableBlueprint($schemaBlueprint, $tableName);

        $schemaBlueprint->addTableBlueprint($tableBlueprint);

        $this->lastTableBlueprint = $tableBlueprint;
    }

    /**
     * @Given /^last TableBlueprint has identity ColumnBlueprint "([^"]*)"$/
     */
    public function lastTableHasIdentityColumnBlueprint($columnName)
    {
        $columnOwner = $this->getLastTableBlueprint();

        $idColumn = new ColumnBlueprint(
            $columnOwner,
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
            $columnOwner,
            'primary_key',
            [$idColumn],
            true,
            false
        );

        $columnOwner->addColumnBlueprint($idColumn);
        $columnOwner->addIndexBlueprint($primaryKey);
    }

    /**
     * @Given /^last TableBlueprint has string ColumnBlueprint "([^"]*)" of length "([^"]*)"$/
     */
    public function lastTableHasStringColumnBlueprintOfLength($columnName, $length)
    {
        $columnOwner = $this->getLastTableBlueprint();

        $titleColumn = new ColumnBlueprint(
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

        $columnOwner->addColumnBlueprint($titleColumn);
    }
}
