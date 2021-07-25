<?php

namespace Tests\Behat\Contexts\Configuration;

use Reliese\Configuration\ConfigurationProfile;
use Tests\Test;
/**
 * Class ConfigurationProfileContext
 */
class ConfigurationProfileContext extends ConfigurationContexts
{
    private ?\Reliese\Configuration\ConfigurationProfile $configurationProfile = null;

    /**
     * @return \Reliese\Configuration\ConfigurationProfile
     */
    public function getConfigurationProfile(): \Reliese\Configuration\ConfigurationProfile
    {

        return $this->configurationProfile ??= (new \Reliese\Configuration\ConfigurationProfileFactory(
            "",
            "",
            $this->loadDefaultRelieseConfigFile(),
        ))->getConfigurationProfile('default');
    }

    private function loadDefaultRelieseConfigFile()
    {
        $appRoot = $this->getConfigurationContexts()->getTemporarySystemDirectory()."/app";

        $configValues = include(__DIR__."/../../Assets/default_reliese_config.php");
        return $configValues;
    }
}