<?php

namespace Tests\Behat\Contexts\Configuration;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Tests\Behat\Contexts\Configuration\DatabaseBlueprintConfigurationContext;
use Tests\Behat\Contexts\FeatureContext;
/**
 * Class ConfigurationContexts
 */
class ConfigurationContexts extends FeatureContext
{

    /** @BeforeScenario
     * This method allows contexts to reference other contexts
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        parent::gatherContexts($scope);
        $environment = $scope->getEnvironment();

        $this->databaseBlueprintConfigurationContext
            = $environment->getContext(DatabaseBlueprintConfigurationContext::class);
        $this->dataTransportGeneratorConfigurationContext
            = $environment->getContext(DataTransportObjectGeneratorConfigurationContext::class);
        $this->modelGeneratorConfigurationContext
            = $environment->getContext(ModelGeneratorConfigurationContext::class);
        $this->relieseConfigurationContext
            = $environment->getContext(RelieseConfigurationContext::class);
    }

    private ?DatabaseBlueprintConfigurationContext $databaseBlueprintConfigurationContext = null;
    private ?DataTransportObjectGeneratorConfigurationContext $dataTransportGeneratorConfigurationContext = null;
    private ?ModelGeneratorConfigurationContext $modelGeneratorConfigurationContext = null;
    private ?RelieseConfigurationContext $relieseConfigurationContext = null;

    /**
     * @return DataTransportObjectGeneratorConfigurationContext
     */
    public function getDataTransportObjectGeneratorConfigurationContext():
    DataTransportObjectGeneratorConfigurationContext
    {
        return $this->dataTransportGeneratorConfigurationContext;
    }

    public function getDatabaseBlueprintConfigurationContext(): DatabaseBlueprintConfigurationContext
    {
        return $this->databaseBlueprintConfigurationContext;
    }

    /**
     * @return ModelGeneratorConfigurationContext
     */
    public function getModelGeneratorConfigurationContext(): ModelGeneratorConfigurationContext
    {
        return $this->modelGeneratorConfigurationContext;
    }

    /**
     * @return RelieseConfigurationContext
     */
    public function getRelieseConfigurationContext(): RelieseConfigurationContext
    {
        return $this->relieseConfigurationContext;
    }

    private ?string $temporarySystemDirectory = null;

    /**
     * @return string
     */
    public function getTemporarySystemDirectory(): string
    {
        return $this->temporarySystemDirectory ??= sys_get_temp_dir();
    }
}