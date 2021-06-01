<?php

namespace Reliese\Generator\Validator;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\WithGeneratedClassDefinitions;
use Reliese\Generator\WithGeneratedObjectTypeDefinitions;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;

/**
 * Class DtoValidatorClassGenerator
 */
class DtoValidatorClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGeneratedClassDefinitions;
    use WithGeneratedObjectTypeDefinitions;
    use WithGetPhpFileDefinitions;
    use WithDtoValidatorAbstractClassGenerator;

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
        return $this->getConfigurationProfile()->getValidatorGeneratorConfiguration()
            ->getClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getValidatorGeneratorConfiguration()
            ->getClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getValidatorGeneratorConfiguration()
            ->getClassSuffix();
    }

    /**
     * @param ColumnOwnerInterface $columnOwner
     *
     * @return ClassDefinition
     */
    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $classDefinition = new ClassDefinition($this->getObjectTypeDefinition($columnOwner));

        $classDefinition
            ->setOriginatingBlueprint($columnOwner)
            ->setParentClass(
                $this->getDtoValidatorAbstractClassGenerator()->getObjectTypeDefinition($columnOwner)
            )
        ;

        return $classDefinition;
    }
}