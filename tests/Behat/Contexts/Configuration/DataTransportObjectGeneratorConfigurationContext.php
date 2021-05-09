<?php

namespace Tests\Behat\Contexts\Configuration;

use Reliese\Configuration\DataTransportObjectGeneratorConfiguration;
/**
 * Class DataTransportObjectGeneratorConfigurationContext
 */
class DataTransportObjectGeneratorConfigurationContext extends ConfigurationContexts
{
    private ?DataTransportObjectGeneratorConfiguration $dataTransportObjectGeneratorConfiguration = null;

    /**
     * @Given /^default DataTransportGeneratorConfiguration$/
     */
    public function defaultDataTransportGeneratorConfiguration()
    {
        /*
         * If one has been customized, use it, otherwise use the default one
         */
        return $this->dataTransportObjectGeneratorConfiguration
            ??= $this->getConfigurationContexts()
                     ->getRelieseConfigurationContext()
                     ->getRelieseConfiguration()
                     ->getDataTransportGeneratorConfiguration();
    }

    public function getDataTransportObjectGeneratorConfiguration(): DataTransportObjectGeneratorConfiguration
    {
        return $this->dataTransportObjectGeneratorConfiguration;
    }
}