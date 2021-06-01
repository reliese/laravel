<?php

namespace Reliese\Configuration;

/**
 * Trait WithConfigurationProfile
 */
trait WithConfigurationProfile
{
    private ?ConfigurationProfile $configurationProfile = null;

    /**
     * @return ConfigurationProfile|null
     */
    protected function getConfigurationProfile(): ConfigurationProfile
    {
        return $this->configurationProfile ??= app()->make(ConfigurationProfile::class);
    }

    /**
     * @param ConfigurationProfile $configurationProfile
     *
     * @return $this
     */
    protected function setConfigurationProfile(ConfigurationProfile $configurationProfile): static
    {
        $this->configurationProfile = $configurationProfile;
        return $this;
    }

}