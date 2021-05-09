<?php

namespace Tests\Behat\Contexts;

use Reliese\Configuration\DatabaseBlueprintConfiguration;
use Tests\Test;

class DatabaseBlueprintConfigurationContext extends FeatureContext
{
    private ?DatabaseBlueprintConfiguration $databaseBlueprintConfiguration;

    /**
     * @return DatabaseBlueprintConfiguration
     */
    public function getDatabaseBlueprintConfiguration(): DatabaseBlueprintConfiguration
    {
        Test::assertInstanceOf(
            DatabaseBlueprintConfiguration::class,
            $this->databaseBlueprintConfiguration,
            'You tried to use a DatabaseBlueprintConfiguration before initializing one.'.
            "\nTry adding 'Given a default DatabaseBlueprintConfiguration' before this statement."
        );

        return $this->databaseBlueprintConfiguration;
    }

    /**
     * @Given /^a default DatabaseBlueprintConfiguration$/
     */
    public function aDefaultDatabaseBlueprintConfiguration()
    {
        $this->databaseBlueprintConfiguration = new DatabaseBlueprintConfiguration([]);
    }
}
