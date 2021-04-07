<?php

namespace Reliese\Configuration;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Reliese\PackagePaths;

/**
 * Class ConfigurationFactory
 */
class RelieseConfigurationFactory
{
    /**
     * @var array
     */
    private array $relieseConfigurationProfiles;

    /**
     * RelieseConfiguration constructor.
     *
     * @param string $appDirectoryPath
     * @param string $configDirectoryPath
     * @param array|null $relieseConfigurationProfiles May not exist if the config has not yet been created
     */
    public function __construct(string $appDirectoryPath,
        string $configDirectoryPath,
        ?array $relieseConfigurationProfiles)
    {
        $this->appDirectoryPath = $appDirectoryPath;
        $this->configDirectoryPath = $configDirectoryPath;
        $this->relieseConfigurationFilePath = $this->configDirectoryPath . DIRECTORY_SEPARATOR . 'reliese.php';
        /*
         * if the config was not provided, fall back on the sample one
         */
        $this->relieseConfigurationProfiles = $relieseConfigurationProfiles ?? include(PackagePaths::getExampleConfigFilePath());
    }

    public function getRelieseConfiguration(string $configurationProfileName): RelieseConfiguration
    {
        Log::info("Creating RelieseConfiguration for Configuration Profile \"$configurationProfileName\"");

        if (!\array_key_exists($configurationProfileName, $this->relieseConfigurationProfiles)) {
            throw new InvalidArgumentException("Unable to locate a configuration profile named $configurationProfileName in {$this->relieseConfigurationFilePath}");
        }

        $configurationProfile = $this->relieseConfigurationProfiles[$configurationProfileName];

        return new RelieseConfiguration($configurationProfileName,
            $this->getDataMapGeneratorConfiguration($configurationProfile),
            $this->getDataTransportGeneratorConfiguration($configurationProfile),
            $this->getDatabaseAnalyserConfiguration($configurationProfile),
            $this->getDatabaseBlueprintConfiguration($configurationProfile),
            $this->getModelGeneratorConfiguration($configurationProfile));
    }

    protected function getDataMapGeneratorConfiguration(array $configurationProfile): DataMapGeneratorConfiguration
    {
        if (!\array_key_exists('DataMapGeneratorConfiguration', $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"DataMapGeneratorConfiguration\"");
        }

        return new DataMapGeneratorConfiguration($configurationProfile['DataMapGeneratorConfiguration']);
    }

    protected function getDataTransportGeneratorConfiguration(array $configurationProfile): DataTransportGeneratorConfiguration
    {
        if (!\array_key_exists('DataTransportGeneratorConfiguration', $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"DataTransportGeneratorConfiguration\"");
        }

        return new DataTransportGeneratorConfiguration($configurationProfile['DataTransportGeneratorConfiguration']);
    }

    protected function getDatabaseAnalyserConfiguration(array $configurationProfile): DatabaseAnalyserConfiguration
    {
        if (!\array_key_exists('DatabaseAnalyserConfiguration', $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"DatabaseAnalyserConfiguration\"");
        }

        return new DatabaseAnalyserConfiguration($configurationProfile['DatabaseAnalyserConfiguration']);
    }

    protected function getDatabaseBlueprintConfiguration(array $configurationProfile): DatabaseBlueprintConfiguration
    {
        if (!\array_key_exists('DatabaseBlueprintConfiguration', $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"DatabaseBlueprintConfiguration\"");
        }

        return new DatabaseBlueprintConfiguration($configurationProfile['DatabaseBlueprintConfiguration']);
    }

    protected function getModelGeneratorConfiguration(array $configurationProfile): ModelGeneratorConfiguration
    {
        if (!\array_key_exists('ModelGeneratorConfiguration', $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"ModelGeneratorConfiguration\"");
        }

        return new ModelGeneratorConfiguration($configurationProfile['ModelGeneratorConfiguration']);
    }
}
