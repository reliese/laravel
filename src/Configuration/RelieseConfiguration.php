<?php

namespace Reliese\Configuration;

/**
 * Class RelieseConfiguration
 */
class RelieseConfiguration
{
    /**
     * @var ModelDataMapGeneratorConfiguration
     */
    protected ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration;

    /**
     * @var DataTransportGeneratorConfiguration
     */
    protected DataTransportGeneratorConfiguration $dataTransportGeneratorConfiguration;

    /**
     * @var DatabaseAnalyserConfiguration
     */
    protected DatabaseAnalyserConfiguration $databaseAnalyserConfiguration;

    /**
     * @var DatabaseBlueprintConfiguration
     */
    protected DatabaseBlueprintConfiguration $databaseBlueprintConfiguration;

    /**
     * @var ModelGeneratorConfiguration
     */
    protected ModelGeneratorConfiguration $modelGeneratorConfiguration;

    /**
     * @var string
     */
    private string $configurationProfileName;

    /**
     * RelieseConfiguration constructor.
     *
     * @param string $configurationProfileName
     * @param ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration
     * @param DataTransportGeneratorConfiguration $dataTransportGeneratorConfiguration
     * @param DatabaseAnalyserConfiguration $databaseAnalyserConfiguration
     * @param DatabaseBlueprintConfiguration $databaseBlueprintConfiguration
     * @param ModelGeneratorConfiguration $modelGeneratorConfiguration
     */
    public function __construct(string $configurationProfileName,
        ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration,
        DataTransportGeneratorConfiguration $dataTransportGeneratorConfiguration,
        DatabaseAnalyserConfiguration $databaseAnalyserConfiguration,
        DatabaseBlueprintConfiguration $databaseBlueprintConfiguration,
        ModelGeneratorConfiguration $modelGeneratorConfiguration,)
    {
        $this->configurationProfileName = $configurationProfileName;
        $this->modelDataMapGeneratorConfiguration = $modelDataMapGeneratorConfiguration;
        $this->dataTransportGeneratorConfiguration = $dataTransportGeneratorConfiguration;
        $this->databaseAnalyserConfiguration = $databaseAnalyserConfiguration;
        $this->databaseBlueprintConfiguration = $databaseBlueprintConfiguration;
        $this->modelGeneratorConfiguration = $modelGeneratorConfiguration;
    }

    /**
     * @return string
     */
    public function getConfigurationProfileName(): string
    {
        return $this->configurationProfileName;
    }

    /**
     * @return ModelDataMapGeneratorConfiguration
     */
    public function getModelDataMapGeneratorConfiguration(): ModelDataMapGeneratorConfiguration
    {
        return $this->modelDataMapGeneratorConfiguration;
    }

    /**
     * @return DataTransportGeneratorConfiguration
     */
    public function getDataTransportGeneratorConfiguration(): DataTransportGeneratorConfiguration
    {
        return $this->dataTransportGeneratorConfiguration;
    }

    /**
     * @return DatabaseAnalyserConfiguration
     */
    public function getDatabaseAnalyserConfiguration(): DatabaseAnalyserConfiguration
    {
        return $this->databaseAnalyserConfiguration;
    }

    /**
     * @return DatabaseBlueprintConfiguration
     */
    public function getDatabaseBlueprintConfiguration(): DatabaseBlueprintConfiguration
    {
        return $this->databaseBlueprintConfiguration;
    }

    /**
     * @return ModelGeneratorConfiguration
     */
    public function getModelGeneratorConfiguration(): ModelGeneratorConfiguration
    {
        return $this->modelGeneratorConfiguration;
    }
}
