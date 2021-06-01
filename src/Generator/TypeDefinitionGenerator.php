<?php

namespace Reliese\Generator;

use Illuminate\Support\Str;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\ClassGeneratorConfigurationInterface;
use Reliese\Configuration\ConfigurationProfile;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
/**
 * Class TypeDefinitionGenerator
 */
class TypeDefinitionGenerator
{
    /**
     * @var ConfigurationProfile
     */
    private ConfigurationProfile $configurationProfile;

    public function __construct(ConfigurationProfile $configurationProfile)
    {
        $this->configurationProfile = $configurationProfile;
    }

    public function rdbmsModelClass(TableBlueprint $tableBlueprint): ObjectTypeDefinition
    {
        $generatorConfiguration = $this->configurationProfile->getModelGeneratorConfiguration();

        return $this->generateObjectTypeDefinition($tableBlueprint, $generatorConfiguration);
    }

    public function rdbmsModelAbstractClass(TableBlueprint $tableBlueprint): ObjectTypeDefinition
    {
        $generatorConfiguration = $this->configurationProfile->getModelGeneratorConfiguration();

        return $this->generateAbstractObjectTypeDefinition($tableBlueprint, $generatorConfiguration);
    }

    public function rdbmsDtoClass(TableBlueprint $tableBlueprint): ObjectTypeDefinition
    {
        $generatorConfiguration = $this->configurationProfile->getDataTransportObjectGeneratorConfiguration();

        return $this->generateObjectTypeDefinition($tableBlueprint, $generatorConfiguration);
    }

    public function rdbmsDtoAbstractClass(TableBlueprint $tableBlueprint): ObjectTypeDefinition
    {
        $generatorConfiguration = $this->configurationProfile->getDataTransportObjectGeneratorConfiguration();

        return $this->generateAbstractObjectTypeDefinition($tableBlueprint, $generatorConfiguration);
    }

    public function rdbmsDataMapClass(TableBlueprint $tableBlueprint): ObjectTypeDefinition
    {
        $generatorConfiguration = $this->configurationProfile->getModelDataMapGeneratorConfiguration();

        return $this->generateObjectTypeDefinition($tableBlueprint, $generatorConfiguration);
    }

    public function rdbmsDataMapAbstractClass(TableBlueprint $tableBlueprint): ObjectTypeDefinition
    {
        $generatorConfiguration = $this->configurationProfile->getModelDataMapGeneratorConfiguration();

        return $this->generateAbstractObjectTypeDefinition($tableBlueprint, $generatorConfiguration);
    }


    public function rdbmsDataAccessClass(TableBlueprint $tableBlueprint): ObjectTypeDefinition
    {
        $generatorConfiguration = $this->configurationProfile->getDataAccessGeneratorConfiguration();

        return $this->generateObjectTypeDefinition($tableBlueprint, $generatorConfiguration);
    }

    public function rdbmsDataAccessAbstractClass(TableBlueprint $tableBlueprint): ObjectTypeDefinition
    {
        $generatorConfiguration = $this->configurationProfile->getDataAccessGeneratorConfiguration();

        return $this->generateAbstractObjectTypeDefinition($tableBlueprint, $generatorConfiguration);
    }

    private function generateObjectTypeDefinition(
        TableBlueprint $tableBlueprint,
        ClassGeneratorConfigurationInterface $generatorConfiguration,
    ): ObjectTypeDefinition {
        $className = Str::studly(Str::singular($tableBlueprint));

        $className = $className . $generatorConfiguration->getClassSuffix();

        return new ObjectTypeDefinition(
            $generatorConfiguration->getClassNamespace() . '\\' . $className);
    }

    /**
     * @param TableBlueprint                       $tableBlueprint
     * @param ClassGeneratorConfigurationInterface $generatorConfiguration
     *
     * @return ObjectTypeDefinition
     */
    private function generateAbstractObjectTypeDefinition(
        TableBlueprint $tableBlueprint,
        ClassGeneratorConfigurationInterface $generatorConfiguration,
    ): ObjectTypeDefinition {
        $className = Str::studly(Str::singular($tableBlueprint));

        $className = $generatorConfiguration->getGeneratedClassPrefix()
            . $className
            . $generatorConfiguration->getClassSuffix();

        return new ObjectTypeDefinition(
            $generatorConfiguration->getParentClassNamespace() . '\\' . $className);
    }
}