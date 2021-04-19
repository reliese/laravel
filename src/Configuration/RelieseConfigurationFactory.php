<?php

namespace Reliese\Configuration;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Reliese\PackagePaths;
use function array_key_exists;

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

    /**
     * @param string $configurationProfileName
     *
     * @return RelieseConfiguration
     */
    public function getRelieseConfiguration(string $configurationProfileName): RelieseConfiguration
    {
        Log::info("Creating RelieseConfiguration for Configuration Profile \"$configurationProfileName\"");

        if (!array_key_exists($configurationProfileName, $this->relieseConfigurationProfiles)) {
            throw new InvalidArgumentException("Unable to locate a configuration profile named $configurationProfileName in {$this->relieseConfigurationFilePath}");
        }

        $configurationProfile = $this->relieseConfigurationProfiles[$configurationProfileName];

        return new RelieseConfiguration($configurationProfileName,
            $this->getModelDataMapGeneratorConfiguration($configurationProfile),
            $this->getDataAccessGeneratorConfiguration($configurationProfile),
            $this->getDataTransportObjectGeneratorConfiguration($configurationProfile),
            $this->getDatabaseAnalyserConfiguration($configurationProfile),
            $this->getDataAttributeGeneratorConfiguration($configurationProfile),
            $this->getDatabaseBlueprintConfiguration($configurationProfile),
            $this->getModelGeneratorConfiguration($configurationProfile));
    }

    /**
     * @param array $configurationProfile
     *
     * @return DataAttributeGeneratorConfiguration
     */
    protected function getDataAttributeGeneratorConfiguration(array $configurationProfile): DataAttributeGeneratorConfiguration
    {
        if (!array_key_exists(DataAttributeGeneratorConfiguration::class, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"DataAttributeGeneratorConfiguration::class\"");
        }

        return new DataAttributeGeneratorConfiguration($configurationProfile[DataAttributeGeneratorConfiguration::class]);
    }

    /**
     * @param array $configurationProfile
     *
     * @return DataTransportObjectGeneratorConfiguration
     */
    protected function getDataTransportObjectGeneratorConfiguration(array $configurationProfile): DataTransportObjectGeneratorConfiguration
    {
        if (!array_key_exists(DataTransportObjectGeneratorConfiguration::class, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"DataTransportObjectGeneratorConfiguration::class\"");
        }

        return new DataTransportObjectGeneratorConfiguration($configurationProfile[DataTransportObjectGeneratorConfiguration::class]);
    }

    /**
     * @param array $configurationProfile
     *
     * @return DatabaseAnalyserConfiguration
     */
    protected function getDatabaseAnalyserConfiguration(array $configurationProfile): DatabaseAnalyserConfiguration
    {
        if (!array_key_exists('DatabaseAnalyserConfiguration', $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"DatabaseAnalyserConfiguration\"");
        }

        return new DatabaseAnalyserConfiguration($configurationProfile['DatabaseAnalyserConfiguration']);
    }

    /**
     * @param array $configurationProfile
     *
     * @return DatabaseBlueprintConfiguration
     */
    protected function getDatabaseBlueprintConfiguration(array $configurationProfile): DatabaseBlueprintConfiguration
    {
        if (!array_key_exists('DatabaseBlueprintConfiguration', $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"DatabaseBlueprintConfiguration\"");
        }

        return new DatabaseBlueprintConfiguration($configurationProfile['DatabaseBlueprintConfiguration']);
    }

    /**
     * @param array $configurationProfile
     *
     * @return ModelDataMapGeneratorConfiguration
     */
    protected function getModelDataMapGeneratorConfiguration(array $configurationProfile): ModelDataMapGeneratorConfiguration
    {
        if (!array_key_exists(ModelDataMapGeneratorConfiguration::class, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"ModelDataMapGeneratorConfiguration::class\"");
        }

        return new ModelDataMapGeneratorConfiguration($configurationProfile[ModelDataMapGeneratorConfiguration::class]);
    }

    /**
     * @param array $configurationProfile
     *
     * @return ModelGeneratorConfiguration
     */
    protected function getModelGeneratorConfiguration(array $configurationProfile): ModelGeneratorConfiguration
    {
        if (!array_key_exists('ModelGeneratorConfiguration', $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"ModelGeneratorConfiguration\"");
        }

        return new ModelGeneratorConfiguration($configurationProfile['ModelGeneratorConfiguration']);
    }

    /**
     * @param mixed $configurationProfile
     *
     * @return DataAccessGeneratorConfiguration
     */
    private function getDataAccessGeneratorConfiguration(mixed $configurationProfile): DataAccessGeneratorConfiguration
    {
        if (!array_key_exists(DataAccessGeneratorConfiguration::class, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"DataAccessGeneratorConfiguration\"");
        }

        return new DataAccessGeneratorConfiguration($configurationProfile[DataAccessGeneratorConfiguration::class]);
    }
}
