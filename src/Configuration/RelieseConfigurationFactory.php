<?php

namespace Reliese\Configuration;

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
     * @var string
     */
    private string $appDirectoryPath;

    /**
     * @var string
     */
    private string $configDirectoryPath;

    /**
     * @var string
     */
    private string $relieseConfigurationFilePath;

    /**
     * RelieseConfiguration constructor.
     *
     * @param string $appDirectoryPath
     * @param string $configDirectoryPath
     * @param array|null $relieseConfigurationProfiles May not exist if the config has not yet been created
     */
    public function __construct(
        string $appDirectoryPath,
        string $configDirectoryPath,
        ?array $relieseConfigurationProfiles
    )
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
// TODO: figure out how to make logging work w/ tests as well
//                Log::info("Creating RelieseConfiguration for Configuration Profile \"$configurationProfileName\"");

        if (!array_key_exists($configurationProfileName, $this->relieseConfigurationProfiles)) {
            throw new InvalidArgumentException("Unable to locate a configuration profile named $configurationProfileName in {$this->relieseConfigurationFilePath}");
        }

        $configurationProfile = $this->relieseConfigurationProfiles[$configurationProfileName];

        return new RelieseConfiguration(
            $configurationProfileName,
            $this->getModelDataMapGeneratorConfiguration($configurationProfile),
            $this->getDataAccessGeneratorConfiguration($configurationProfile),
            $this->getDataTransportObjectGeneratorConfiguration($configurationProfile),
            $this->getDatabaseAnalyserConfiguration($configurationProfile),
            $this->getDataAttributeGeneratorConfiguration($configurationProfile),
            $this->getDatabaseBlueprintConfiguration($configurationProfile),
            $this->getModelGeneratorConfiguration($configurationProfile)
        );
    }

    /**
     * @param array $configurationProfile
     *
     * @return DataAttributeGeneratorConfiguration
     */
    protected function getDataAttributeGeneratorConfiguration(array $configurationProfile): DataAttributeGeneratorConfiguration
    {
        $key = DataAttributeGeneratorConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new DataAttributeGeneratorConfiguration($configurationProfile[$key]);
    }

    /**
     * @param array $configurationProfile
     *
     * @return DataTransportObjectGeneratorConfiguration
     */
    protected function getDataTransportObjectGeneratorConfiguration(array $configurationProfile): DataTransportObjectGeneratorConfiguration
    {

        $key = DataTransportObjectGeneratorConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new DataTransportObjectGeneratorConfiguration($configurationProfile[$key]);
    }

    /**
     * @param array $configurationProfile
     *
     * @return DatabaseAnalyserConfiguration
     */
    protected function getDatabaseAnalyserConfiguration(array $configurationProfile): DatabaseAnalyserConfiguration
    {
        $key = DatabaseAnalyserConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new DatabaseAnalyserConfiguration($configurationProfile[$key]);
    }

    /**
     * @param array $configurationProfile
     *
     * @return DatabaseBlueprintConfiguration
     */
    protected function getDatabaseBlueprintConfiguration(array $configurationProfile): DatabaseBlueprintConfiguration
    {
        $key = DatabaseBlueprintConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new DatabaseBlueprintConfiguration($configurationProfile[$key]);
    }

    /**
     * @param array $configurationProfile
     *
     * @return ModelDataMapGeneratorConfiguration
     */
    protected function getModelDataMapGeneratorConfiguration(array $configurationProfile): ModelDataMapGeneratorConfiguration
    {
        $key = ModelDataMapGeneratorConfiguration::class;
        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"$key\"");
        }

        return new ModelDataMapGeneratorConfiguration($configurationProfile[$key]);
    }

    /**
     * @param array $configurationProfile
     *
     * @return ModelGeneratorConfiguration
     */
    protected function getModelGeneratorConfiguration(array $configurationProfile): ModelGeneratorConfiguration
    {
        $key = ModelGeneratorConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new ModelGeneratorConfiguration($configurationProfile[$key]);
    }

    /**
     * @param mixed $configurationProfile
     *
     * @return DataAccessGeneratorConfiguration
     */
    private function getDataAccessGeneratorConfiguration(mixed $configurationProfile): DataAccessGeneratorConfiguration
    {
        $key = DataAccessGeneratorConfiguration::class;
        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"$key\"");
        }

        return new DataAccessGeneratorConfiguration($configurationProfile[$key]);
    }
}
