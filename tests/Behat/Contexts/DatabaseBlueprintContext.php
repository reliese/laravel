<?php

namespace Tests\Behat\Contexts;

use Reliese\Blueprint\DatabaseBlueprint;
use Tests\Test;

class DatabaseBlueprintContext extends FeatureContext
{
    private ?DatabaseBlueprint $databaseBlueprint;

    /**
     * @return DatabaseBlueprint
     */
    public function getDatabaseBlueprint(): DatabaseBlueprint
    {
        Test::assertInstanceOf(
            DatabaseBlueprint::class,
            $this->databaseBlueprint,
            'You tried to use a DatabaseBlueprint before initializing one'
        );

        return $this->databaseBlueprint;
    }

    /**
     * @Given /^a new DatabaseBlueprint$/
     */
    public function aNewDatabaseBlueprint()
    {
        $this->databaseBlueprint = new DatabaseBlueprint(
            $this->databaseBlueprintConfigurationContext->getDatabaseBlueprintConfiguration()
        );
    }
}
