<?php

namespace Reliese\Generator\DataTransport;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Database\WithPhpTypeMap;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;

/**
 * Class DataTransportCollectionAbstractClassGenerator
 */
class DataTransportCollectionAbstractClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;
    use WithPhpTypeMap;
    use WithDataTransportCollectionClassGenerator;

    /** @var ClassPropertyDefinition[] */
    private array $generatedForeignKeyDtoPropertyDefinitions = [];

    protected function allowClassFileOverwrite(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()->getDataTransportCollectionGeneratorConfiguration()
            ->getGeneratedClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getDataTransportCollectionGeneratorConfiguration()
            ->getGeneratedClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getDataTransportCollectionGeneratorConfiguration()
            ->getGeneratedClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        // TODO: Implement generateClassDefinition() method.
        throw new \Exception(__METHOD__ . " has not been implemented.");
    }
}
