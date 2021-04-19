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
     * @var DataTransportObjectGeneratorConfiguration
     */
    protected DataTransportObjectGeneratorConfiguration $dataTransportGeneratorConfiguration;

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
     * @var DataAccessGeneratorConfiguration
     */
    private DataAccessGeneratorConfiguration $dataAccessGeneratorConfiguration;

    /**
     * @var DataAttributeGeneratorConfiguration
     */
    private DataAttributeGeneratorConfiguration $dataAttributeGeneratorConfiguration;

    /**
     * RelieseConfiguration constructor.
     *
     * @param string $configurationProfileName
     * @param ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration
     * @param DataAccessGeneratorConfiguration $dataAccessGeneratorConfiguration
     * @param DataTransportObjectGeneratorConfiguration $dataTransportGeneratorConfiguration
     * @param DatabaseAnalyserConfiguration $databaseAnalyserConfiguration
     * @param DataAttributeGeneratorConfiguration $dataAttributeGeneratorConfiguration
     * @param DatabaseBlueprintConfiguration $databaseBlueprintConfiguration
     * @param ModelGeneratorConfiguration $modelGeneratorConfiguration
     */
    public function __construct(string $configurationProfileName,
        ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration,
        DataAccessGeneratorConfiguration $dataAccessGeneratorConfiguration,
        DataTransportObjectGeneratorConfiguration $dataTransportGeneratorConfiguration,
        DatabaseAnalyserConfiguration $databaseAnalyserConfiguration,
        DataAttributeGeneratorConfiguration $dataAttributeGeneratorConfiguration,
        DatabaseBlueprintConfiguration $databaseBlueprintConfiguration,
        ModelGeneratorConfiguration $modelGeneratorConfiguration,)
    {
        $this->configurationProfileName = $configurationProfileName;
        $this->modelDataMapGeneratorConfiguration = $modelDataMapGeneratorConfiguration;
        $this->dataAccessGeneratorConfiguration = $dataAccessGeneratorConfiguration;
        $this->dataTransportGeneratorConfiguration = $dataTransportGeneratorConfiguration;
        $this->databaseAnalyserConfiguration = $databaseAnalyserConfiguration;
        $this->databaseBlueprintConfiguration = $databaseBlueprintConfiguration;
        $this->modelGeneratorConfiguration = $modelGeneratorConfiguration;
        $this->dataAttributeGeneratorConfiguration = $dataAttributeGeneratorConfiguration;
    }

    /**
     * @return string
     */
    public function getConfigurationProfileName(): string
    {
        return $this->configurationProfileName;
    }

    public function getDataAccessGeneratorConfiguration()
    {
        return $this->dataAccessGeneratorConfiguration;
    }

    /**
     * @return DataAttributeGeneratorConfiguration
     */
    public function getDataAttributeGeneratorConfiguration(): DataAttributeGeneratorConfiguration
    {
        return $this->dataAttributeGeneratorConfiguration;
    }

    /**
     * @return ModelDataMapGeneratorConfiguration
     */
    public function getModelDataMapGeneratorConfiguration(): ModelDataMapGeneratorConfiguration
    {
        return $this->modelDataMapGeneratorConfiguration;
    }

    /**
     * @return DataTransportObjectGeneratorConfiguration
     */
    public function getDataTransportGeneratorConfiguration(): DataTransportObjectGeneratorConfiguration
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
