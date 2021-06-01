<?php

namespace Reliese\Generator\Model;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\Model\ModelAbstractClassGenerator;
use Reliese\Generator\WithGeneratedClassDefinitions;
use Reliese\Generator\WithGeneratedObjectTypeDefinitions;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;
/**
 * Class ModelClassGenerator
 */
class ModelClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGeneratedClassDefinitions;
    use WithGeneratedObjectTypeDefinitions;
    use WithGetPhpFileDefinitions;
    use WithModelAbstractClassGenerator;

    protected function allowClassFileOverwrite(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()->getModelGeneratorConfiguration()
            ->getClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getModelGeneratorConfiguration()
            ->getClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getModelGeneratorConfiguration()
            ->getClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $classDefinition = new ClassDefinition($this->getObjectTypeDefinition($columnOwner));

        $classDefinition
            ->setOriginatingBlueprint($columnOwner)
            ->setParentClass(
                $this->getModelAbstractClassGenerator()->getObjectTypeDefinition($columnOwner)
            )
        ;

        return $classDefinition;
    }
}