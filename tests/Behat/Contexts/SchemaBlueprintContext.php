<?php

namespace Tests\Behat\Contexts;

use Reliese\Blueprint\SchemaBlueprint;
use Tests\Test;

class SchemaBlueprintContext extends FeatureContext
{
    /**
     * @var SchemaBlueprint[]
     */
    private array $schemaBlueprints = [];

    /**
     * @param string $schemaName
     *
     * @return SchemaBlueprint
     */
    public function getSchemaBlueprint(string $schemaName): SchemaBlueprint
    {
        Test::assertArrayHasKey(
            $schemaName,
            $this->schemaBlueprints
            // TODO: Add meaningful message
        );

        return $this->schemaBlueprints[$schemaName];
    }

    /**
     * @Given /^the DatabaseBlueprint has SchemaBlueprint "([^"]*)"$/
     */
    public function theDatabaseBlueprintHasSchemaBlueprint($schemaName)
    {
        $this->schemaBlueprints[$schemaName] = new SchemaBlueprint(
            $this->databaseBlueprintContext->getDatabaseBlueprint(),
            $schemaName
        );
    }
}
