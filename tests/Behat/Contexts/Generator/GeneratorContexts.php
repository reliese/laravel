<?php

namespace Tests\Behat\Contexts\Generator;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Tests\Behat\Contexts\Generator\DataTransportObjectGeneratorContext;
use Tests\Behat\Contexts\FeatureContext;
/**
 * Class GeneratorContexts
 */
class GeneratorContexts extends FeatureContext
{

    /** @BeforeScenario
     * This method allows contexts to reference other contexts
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        parent::gatherContexts($scope);
        $environment = $scope->getEnvironment();

        $this->dataTransportGeneratorGeneratorContext
            = $environment->getContext(DataTransportObjectGeneratorContext::class);

    }

    private ?DataTransportObjectGeneratorContext $dataTransportGeneratorGeneratorContext = null;

    /**
     * @return DataTransportObjectGeneratorContext
     */
    public function getDataTransportObjectGeneratorContext(): DataTransportObjectGeneratorContext
    {
        return $this->dataTransportGeneratorGeneratorContext;
    }
}