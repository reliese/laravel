<?php

namespace Tests\Behat\Contexts\Configuration;

use Reliese\Configuration\RelieseConfiguration;
use Tests\Test;
/**
 * Class RelieseConfigurationContext
 */
class RelieseConfigurationContext extends ConfigurationContexts
{
    private ?\Reliese\Configuration\RelieseConfiguration $relieseConfiguration = null;

    /**
     * @return \Reliese\Configuration\RelieseConfiguration
     */
    public function getRelieseConfiguration(): \Reliese\Configuration\RelieseConfiguration
    {

        return $this->relieseConfiguration ??= (new \Reliese\Configuration\RelieseConfigurationFactory(
            "",
            "",
            $this->loadDefaultRelieseConfigFile(),
        ))->getRelieseConfiguration('default');
    }

    private function loadDefaultRelieseConfigFile()
    {
        $appRoot = $this->getConfigurationContexts()->getTemporarySystemDirectory()."/app";

        $configValues = include(__DIR__."/../../Assets/default_reliese_config.php");
        return $configValues;
    }
}