<?php

namespace Reliese\Generator\Model;

use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Database\WithPhpTypeMap;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\WithGeneratedClassDefinitions;
use Reliese\Generator\WithGeneratedObjectTypeDefinitions;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassConstantDefinition;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Enum\AbstractEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Tool\ClassNameTool;
/**
 * Class ModelAbstractClassGenerator
 */
class ModelAbstractClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGeneratedClassDefinitions;
    use WithGeneratedObjectTypeDefinitions;
    use WithGetPhpFileDefinitions;
    use WithPhpTypeMap;
    use WithModelClassGenerator;

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
        return $this->getConfigurationProfile()->getDataAccessGeneratorConfiguration()
            ->getGeneratedClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getDataAccessGeneratorConfiguration()
            ->getGeneratedClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getDataAccessGeneratorConfiguration()
            ->getGeneratedClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $abstractClassDefinition = new ClassDefinition(
            $this->getObjectTypeDefinition($columnOwner),
            AbstractEnum::abstractEnum()
        );


        return $abstractClassDefinition;
    }

    /**
     * @param ColumnBlueprint $columnBlueprint
     *
     * @return ClassConstantDefinition
     */
    public function generateColumnConstantDefinition(ColumnBlueprint $columnBlueprint): ClassConstantDefinition
    {
        return new ClassConstantDefinition(
            ClassNameTool::columnNameToConstantName($columnBlueprint->getColumnName()),
            $columnBlueprint->getColumnName(),
            VisibilityEnum::publicEnum()
        );
    }
}