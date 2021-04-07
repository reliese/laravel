<?php

namespace Reliese\Generator\Model;

use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\ModelGeneratorConfiguration;
use Reliese\MetaCode\Tool\ClassNameTool;

/**
 * Class ModelGenerator
 */
class ModelGenerator
{
    private ModelGeneratorConfiguration $modelGeneratorConfiguration;

    public function __construct(ModelGeneratorConfiguration $modelGeneratorConfiguration)
    {
        $this->modelGeneratorConfiguration = $modelGeneratorConfiguration;
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getFullyQualifiedClassName(TableBlueprint $tableBlueprint): string
    {
        return $this->getClassNamespace($tableBlueprint).'\\'.$this->getClassName($tableBlueprint);
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getClassNamespace(TableBlueprint $tableBlueprint): string
    {
        return $this->modelGeneratorConfiguration->getNamespace();
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getClassName(TableBlueprint $tableBlueprint): string
    {
        return ClassNameTool::snakeCaseToClassName(
            null,
            $tableBlueprint->getName(),
            $this->modelGeneratorConfiguration->getClassSuffix()
        );
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getAbstractClassName(TableBlueprint $tableBlueprint): string
    {
        return $this->modelGeneratorConfiguration->getParentClassPrefix()
            . $this->getClassName($tableBlueprint);
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getAbstractClassNamespace(TableBlueprint $tableBlueprint): string
    {
        return $this->getClassNamespace($tableBlueprint) .'\\Generated';
    }
}
