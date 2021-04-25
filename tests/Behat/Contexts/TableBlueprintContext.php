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
     * @Given /^last table has identity ColumnBlueprint "([^"]*)"$/
     */
    public function lastTableHasIdentityColumnBlueprint($columnName)
    {

        $idColumn = new ColumnBlueprint(
            $this->getLastTableBlueprint(),
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
            $this->getLastTableBlueprint(),
            'primary_key',
            [$idColumn],
            true,
            false
        );

        $this->getLastTableBlueprint()->addColumnBlueprint($idColumn);
        $this->getLastTableBlueprint()->addIndexBlueprint($primaryKey);
    }

    /**
     * @Given /^last table has string ColumnBlueprint "([^"]*)" of length "([^"]*)"$/
     */
    public function lastTableHasStringColumnBlueprintOfLength($columnName, $length)
    {

        $titleColumn = new ColumnBlueprint(
            $this->getLastTableBlueprint(),
            $columnName,
            'string',
            false,
            $length,
            -1,
            -1,
            false,
            false
        );

        $this->getLastTableBlueprint()->addColumnBlueprint($titleColumn);
    }
}
