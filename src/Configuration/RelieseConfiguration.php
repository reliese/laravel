<?php

namespace Reliese\Configuration;

/**
 * Class RelieseConfiguration
 */
class RelieseConfiguration
{
    /**
     * @var DataMapGeneratorConfiguration
     */
    protected DataMapGeneratorConfiguration $dataMapGeneratorConfiguration;

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
     * @param DataMapGeneratorConfiguration $dataMapGeneratorConfiguration
     * @param DataTransportGeneratorConfiguration $dataTransportGeneratorConfiguration
     * @param DatabaseAnalyserConfiguration $databaseAnalyserConfiguration
     * @param DatabaseBlueprintConfiguration $databaseBlueprintConfiguration
     * @param ModelGeneratorConfiguration $modelGeneratorConfiguration
     */
    public function __construct(string $configurationProfileName,
        DataMapGeneratorConfiguration $dataMapGeneratorConfiguration,
        DataTransportGeneratorConfiguration $dataTransportGeneratorConfiguration,
        DatabaseAnalyserConfiguration $databaseAnalyserConfiguration,
        DatabaseBlueprintConfiguration $databaseBlueprintConfiguration,
        ModelGeneratorConfiguration $modelGeneratorConfiguration,)
    {
        $this->configurationProfileName = $configurationProfileName;
        $this->dataMapGeneratorConfiguration = $dataMapGeneratorConfiguration;
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
     * @return DataMapGeneratorConfiguration
     */
    public function getDataMapGeneratorConfiguration(): DataMapGeneratorConfiguration
    {
        return $this->dataMapGeneratorConfiguration;
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
