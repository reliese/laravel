<?php

namespace Tests\Behat\Contexts\Configuration;

use Reliese\Configuration\DatabaseBlueprintConfiguration;
use Tests\Behat\Contexts\FeatureContext;
use Tests\Test;

class DatabaseBlueprintConfigurationContext extends FeatureContext
{
    private ?DatabaseBlueprintConfiguration $databaseBlueprintConfiguration = null;

    /**
     * @return DatabaseBlueprintConfiguration
     */
    public function getDatabaseBlueprintConfiguration(): DatabaseBlueprintConfiguration
    {
        return $this->databaseBlueprintConfiguration
            ??= $this->getConfigurationContexts()
                            ->getRelieseConfigurationContext()
                            ->getRelieseConfiguration()
                            ->getDatabaseBlueprintConfiguration();
    }

    /**
     * @Given /^a default DatabaseBlueprintConfiguration$/
     */
    public function aDefaultDatabaseBlueprintConfiguration()
    {
        $this->databaseBlueprintConfiguration = new DatabaseBlueprintConfiguration([]);
    }
}
