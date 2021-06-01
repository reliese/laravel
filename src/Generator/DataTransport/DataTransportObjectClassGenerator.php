<?php

namespace Reliese\Generator\DataTransport;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;
/**
 * Class DataTransportObjectClassGenerator
 */
class DataTransportObjectClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;
    use WithDataTransportObjectAbstractClassGenerator;

    /**
     * @var DataTransportObjectAbstractClassGenerator
     */
    protected DataTransportObjectAbstractClassGenerator $abstractClassGenerator;

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
                $this->getDataTransportObjectAbstractClassGenerator()->getObjectTypeDefinition($columnOwner)
            )
        ;

        return $classDefinition;
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
        return $this->getConfigurationProfile()->getDataTransportObjectGeneratorConfiguration()
            ->getClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getDataTransportObjectGeneratorConfiguration()
            ->getClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getDataTransportObjectGeneratorConfiguration()
            ->getClassSuffix();
    }
}