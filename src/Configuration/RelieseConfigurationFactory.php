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
            $this->getDataMapGeneratorConfiguration($configurationProfile),
            $this->getDataTransportGeneratorConfiguration($configurationProfile),
            $this->getDatabaseAnalyserConfiguration($configurationProfile),
            $this->getDatabaseBlueprintConfiguration($configurationProfile),
            $this->getModelGeneratorConfiguration($configurationProfile)
        );
    }

    protected function getDataMapGeneratorConfiguration(array $configurationProfile): ModelDataMapGeneratorConfiguration
    {
        $key = ModelDataMapGeneratorConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new ModelDataMapGeneratorConfiguration($configurationProfile[$key]);
    }

    protected function getDataTransportGeneratorConfiguration(array $configurationProfile): DataTransportGeneratorConfiguration
    {
        $key = DataTransportGeneratorConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new DataTransportGeneratorConfiguration($configurationProfile[$key]);
    }

    protected function getDatabaseAnalyserConfiguration(array $configurationProfile): DatabaseAnalyserConfiguration
    {
        $key = DatabaseAnalyserConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new DatabaseAnalyserConfiguration($configurationProfile[$key]);
    }

    protected function getDatabaseBlueprintConfiguration(array $configurationProfile): DatabaseBlueprintConfiguration
    {
        $key = DatabaseBlueprintConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new DatabaseBlueprintConfiguration($configurationProfile[$key]);
    }

    protected function getModelGeneratorConfiguration(array $configurationProfile): ModelGeneratorConfiguration
    {
        $key = ModelGeneratorConfiguration::class;

        if (!array_key_exists($key, $configurationProfile)) {
            throw new InvalidArgumentException("Unable to locate configuration block for \"{$key}\"");
        }

        return new ModelGeneratorConfiguration($configurationProfile[$key]);
    }
}
