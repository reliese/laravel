<?php

namespace Tests\Behat\Contexts\Blueprint;

use Behat\Behat\Tester\Exception\PendingException;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\SchemaBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Tests\Behat\Contexts\FeatureContext;
use Tests\Test;

class DatabaseBlueprintContext extends BlueprintContexts
{
    private ?DatabaseBlueprint $databaseBlueprint = null;

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
            $this->getConfigurationContexts()
            ->getDatabaseBlueprintConfigurationContext()
            ->getDatabaseBlueprintConfiguration()
        );
    }
}
