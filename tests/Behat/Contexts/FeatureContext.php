<?php

namespace Tests\Behat\Contexts;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Tests\Behat\Contexts\Blueprint\BlueprintContexts;
use Tests\Behat\Contexts\Blueprint\DatabaseBlueprintContext;
use Tests\Behat\Contexts\Configuration\ConfigurationContexts;
use Tests\Behat\Contexts\Generator\GeneratorContexts;
use const PHP_EOL;

class FeatureContext implements Context
{
    protected ClassDefinitionContext $classDefinitionContext;

    private BlueprintContexts $blueprintContexts;

    private ConfigurationContexts $configurationContexts;

    private GeneratorContexts $generatorContexts;

    /** @BeforeScenario
     * This method allows contexts to reference other contexts
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->configurationContexts = $environment->getContext(ConfigurationContexts::class);
        $this->blueprintContexts = $environment->getContext(BlueprintContexts::class);

        $this->generatorContexts = $environment->getContext(GeneratorContexts::class);
        $this->classDefinitionContext = $environment->getContext(ClassDefinitionContext::class);
    }

    /**
     * @return ConfigurationContexts
     */
    public function getConfigurationContexts(): ConfigurationContexts
    {
        return $this->configurationContexts;
    }

    /**
     * @return BlueprintContexts
     */
    public function getBlueprintContexts(): BlueprintContexts
    {
        return $this->blueprintContexts;
    }

    /**
     * @return GeneratorContexts
     */
    public function getGeneratorContexts(): GeneratorContexts
    {
        return $this->generatorContexts;
    }
}
