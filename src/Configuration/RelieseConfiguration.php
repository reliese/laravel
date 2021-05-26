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
     * @var CodeFormattingConfiguration
     */
    private CodeFormattingConfiguration $codeFormattingConfiguration;

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
     * @var ValidatorGeneratorConfiguration
     */
    private ValidatorGeneratorConfiguration $validatorGeneratorConfiguration;

    /**
     * RelieseConfiguration constructor.
     *
     * @param string                                    $configurationProfileName
     * @param CodeFormattingConfiguration               $codeFormattingConfiguration
     * @param ModelDataMapGeneratorConfiguration        $modelDataMapGeneratorConfiguration
     * @param DataAccessGeneratorConfiguration          $dataAccessGeneratorConfiguration
     * @param DataTransportObjectGeneratorConfiguration $dataTransportGeneratorConfiguration
     * @param DatabaseAnalyserConfiguration             $databaseAnalyserConfiguration
     * @param DataAttributeGeneratorConfiguration       $dataAttributeGeneratorConfiguration
     * @param DatabaseBlueprintConfiguration            $databaseBlueprintConfiguration
     * @param ModelGeneratorConfiguration               $modelGeneratorConfiguration
     * @param ValidatorGeneratorConfiguration           $validatorGeneratorConfiguration
     */
    public function __construct(string $configurationProfileName,
        CodeFormattingConfiguration $codeFormattingConfiguration,
        ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration,
        DataAccessGeneratorConfiguration $dataAccessGeneratorConfiguration,
        DataTransportObjectGeneratorConfiguration $dataTransportGeneratorConfiguration,
        DatabaseAnalyserConfiguration $databaseAnalyserConfiguration,
        DataAttributeGeneratorConfiguration $dataAttributeGeneratorConfiguration,
        DatabaseBlueprintConfiguration $databaseBlueprintConfiguration,
        ModelGeneratorConfiguration $modelGeneratorConfiguration,
        ValidatorGeneratorConfiguration $validatorGeneratorConfiguration,
    )
    {
        $this->configurationProfileName = $configurationProfileName;
        $this->codeFormattingConfiguration = $codeFormattingConfiguration;
        $this->modelDataMapGeneratorConfiguration = $modelDataMapGeneratorConfiguration;
        $this->dataAccessGeneratorConfiguration = $dataAccessGeneratorConfiguration;
        $this->dataTransportGeneratorConfiguration = $dataTransportGeneratorConfiguration;
        $this->databaseAnalyserConfiguration = $databaseAnalyserConfiguration;
        $this->databaseBlueprintConfiguration = $databaseBlueprintConfiguration;
        $this->modelGeneratorConfiguration = $modelGeneratorConfiguration;
        $this->dataAttributeGeneratorConfiguration = $dataAttributeGeneratorConfiguration;
        $this->validatorGeneratorConfiguration = $validatorGeneratorConfiguration;
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

    public function getValidatorGeneratorConfiguration()
    {
        return $this->validatorGeneratorConfiguration;
    }

    /**
     * @return CodeFormattingConfiguration
     */
    public function getCodeFormattingConfiguration(): CodeFormattingConfiguration
    {
        return $this->codeFormattingConfiguration;
    }
}
