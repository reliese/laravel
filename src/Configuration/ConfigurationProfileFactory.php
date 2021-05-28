<?php

namespace Reliese\Configuration;

use function array_key_exists;
/**
 * Class ConfigurationProfileFactory
 */
class ConfigurationProfileFactory
{
    protected string $applicationPath;

    /**
     * @var ConfigurationProfile[]
     */
    protected array $configurationProfiles;

    protected ConfigurationProfile $activeConfigurationProfile;

    /**
     * ConfigurationProfileFactory constructor.
     *
     * @param string $applicationPath
     * @param array  $configurationProfiles
     */
    public function __construct(string $applicationPath, array $configurationProfiles)
    {
        $this->applicationPath = $applicationPath;
        $this->parseConfigurationProfiles($configurationProfiles);
    }

    /**
     * @return string
     */
    public function getApplicationPath(): string
    {
        return $this->applicationPath;
    }

    /**
     * @param string $applicationPath
     *
     * @return ConfigurationProfileFactory
     */
    public function setApplicationPath(string $applicationPath): ConfigurationProfileFactory
    {
        $this->applicationPath = $applicationPath;
        return $this;
    }

    /**
     * @return ConfigurationProfile[]
     */
    public function getConfigurationProfiles(): array
    {
        return $this->configurationProfiles;
    }

    /**
     * @param ConfigurationProfile $configurationProfile
     *
     * @return $this
     */
    public function addConfigurationProfile(ConfigurationProfile $configurationProfile): ConfigurationProfileFactory
    {
        $this->configurationProfiles[$configurationProfile->getName()] = $configurationProfile;
        return $this;
    }

    /**
     * @param string $profileName
     *
     * @return ConfigurationProfile
     */
    public function getConfigurationByProfileName(string $profileName): ConfigurationProfile
    {
        return $this->configurationProfiles[$profileName];
    }

    /**
     * @param string $profileName
     *
     * @return bool
     */
    public function hasConfigurationByProfileName(string $profileName): bool
    {
        return array_key_exists($profileName, $this->configurationProfiles);
    }

    /**
     * @param array $configurationProfiles
     *
     * @return $this
     */
    public function parseConfigurationProfiles(array $configurationProfiles): static
    {
        foreach ($configurationProfiles as $name => $configurationProfile) {
            $this->addConfigurationProfile(new ConfigurationProfile($name, $configurationProfile));
        }

        return $this;
    }

    /**
     * @return ConfigurationProfile
     */
    public function getActiveConfigurationProfile(): ConfigurationProfile
    {
        return $this->activeConfigurationProfile;
    }

    /**
     * @param ConfigurationProfile $activeConfigurationProfile
     *
     * @return ConfigurationProfileFactory
     */
    public function setActiveConfigurationProfile(ConfigurationProfile $activeConfigurationProfile): ConfigurationProfileFactory
    {
        $this->activeConfigurationProfile = $activeConfigurationProfile;
        return $this;
    }

    /**
     * @param string $configurationProfileName
     *
     * @return ConfigurationProfileFactory
     */
    public function setActiveConfigurationProfileByName(string $configurationProfileName): ConfigurationProfileFactory
    {
        $this->activeConfigurationProfile = $this->getConfigurationByProfileName($configurationProfileName);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasActiveConfigurationProfile()
    {
        return $this->activeConfigurationProfile instanceof ConfigurationProfile;
    }
}