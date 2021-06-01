<?php

namespace Reliese\Generator\DataMap;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Database\WithPhpTypeMap;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\Model\WithModelClassGenerator;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;

/**
 * Class ModelDataMapClassGenerator
 */
class ModelDataMapClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;
    use WithPhpTypeMap;
    use WithModelClassGenerator;
    use WithModelDataMapAbstractClassGenerator;

    /**
     * @return bool
     */
    protected function allowClassFileOverwrite(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $classDefinition = new ClassDefinition($this->getObjectTypeDefinition($columnOwner));

        $classDefinition
            ->setOriginatingBlueprint($columnOwner)
            ->setParentClass(
                $this->getModelDataMapAbstractClassGenerator()->getObjectTypeDefinition($columnOwner)
            )
        ;

        return $classDefinition;
    }
}