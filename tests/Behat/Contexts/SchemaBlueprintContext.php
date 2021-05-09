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

    private ?SchemaBlueprint $lastSchemaBlueprint = null;

    /**
     * @param string $schemaName
     *
     * @return SchemaBlueprint
     */
    public function getSchemaBlueprint(string $schemaName): SchemaBlueprint
    {
        Test::assertArrayHasKey(
            $schemaName,
            $this->schemaBlueprints,
            "You tried to use a SchemaBlueprint [{$schemaName}] before registering it.".
            "\nTry adding 'the DatabaseBlueprint has SchemaBlueprint \"{$schemaName}\"' before this statement."
        );

        return $this->lastSchemaBlueprint = $this->schemaBlueprints[$schemaName];
    }

    /**
     * @return SchemaBlueprint
     */
    public function getLastSchemaBlueprint(): SchemaBlueprint
    {
        Test::assertInstanceOf(
            SchemaBlueprint::class,
            $this->lastSchemaBlueprint,
            "You tried to reference a previously initialized SchemaBlueprint, but none has been constructed.".
            "\nTry adding 'the DatabaseBlueprint has SchemaBlueprint \"some_squema\"' before this statement."
        );

        return $this->lastSchemaBlueprint;
    }

    /**
     * @Given /^the DatabaseBlueprint has SchemaBlueprint "([^"]*)"$/
     */
    public function theDatabaseBlueprintHasSchemaBlueprint($schemaName)
    {
        $this->schemaBlueprints[$schemaName] = new SchemaBlueprint(
            $this->databaseBlueprintContext->getDatabaseBlueprint(),
            $schemaName,
            'some_connection'
        );
    }
}
