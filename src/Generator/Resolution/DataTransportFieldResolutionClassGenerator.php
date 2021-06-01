<?php

namespace Reliese\Generator\Resolution;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;

/**
 * Class DataTransportFieldResolutionClassGenerator
 */
class DataTransportFieldResolutionClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;

    /**
     * @var DataTransportFieldResolutionAbstractClassGenerator
     */
    protected DataTransportFieldResolutionAbstractClassGenerator $abstractClassGenerator;

    /**
     * DtoResolverClassGenerator constructor.
     *
     * @param DataTransportFieldResolutionAbstractClassGenerator $abstractClassGenerator
     */
    public function __construct(DataTransportFieldResolutionAbstractClassGenerator $abstractClassGenerator)
    {
        $this->abstractClassGenerator = $abstractClassGenerator;
    }

    protected function allowClassFileOverwrite(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()->getResolverGeneratorConfiguration()
            ->getClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getResolverGeneratorConfiguration()
            ->getClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getResolverGeneratorConfiguration()
            ->getClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $classDefinition = new ClassDefinition($this->getObjectTypeDefinition($columnOwner));

        $classDefinition
            ->setOriginatingBlueprint($columnOwner)
            ->setParentClass(
                $this->abstractClassGenerator->getObjectTypeDefinition($columnOwner)
            )
        ;

        return $classDefinition;
    }
}