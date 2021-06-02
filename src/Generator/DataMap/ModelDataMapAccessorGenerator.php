<?php

namespace Reliese\Generator\DataMap;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Database\WithPhpTypeMap;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\Model\WithModelClassGenerator;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Definition\StatementDefinitionCollection;
use Reliese\MetaCode\Definition\TraitDefinition;
use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Tool\ClassNameTool;
use function sprintf;
/**
 * Class ModelDataMapAccessorGenerator
 */
class ModelDataMapAccessorGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;
    use WithPhpTypeMap;
    use WithModelClassGenerator;
    use WithModelDataMapClassGenerator;

    /**
     * @var ModelDataMapAbstractClassGenerator
     */
    protected ModelDataMapAbstractClassGenerator $abstractClassGenerator;

    /**
     * ModelDataMapClassGenerator constructor.
     *
     * @param ModelDataMapAbstractClassGenerator $abstractClassGenerator
     */
    public function __construct(ModelDataMapAbstractClassGenerator $abstractClassGenerator)
    {
        $this->abstractClassGenerator = $abstractClassGenerator;
    }

    protected function allowClassFileOverwrite(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getAccessorTraitNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getAccessorTraitPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getAccessorTraitSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        return $this->generateModelDataMapAccessorTrait(
            $this->getModelDataMapClassGenerator()->getObjectTypeDefinition($columnOwner)->getFullyQualifiedName()
        );
    }

    public function generateModelDataMapAccessorTrait(string $fullyQualifiedModelDataMapClass) : TraitDefinition
    {
        $dataMapClass = ClassNameTool::fullyQualifiedClassNameToClassName($fullyQualifiedModelDataMapClass);
        $dataMapNamespace = ClassNameTool::fullyQualifiedClassNameToNamespace($fullyQualifiedModelDataMapClass);

        $namespace = $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getAccessorTraitNamespace();
        $className = ClassNameTool::snakeCaseToClassName('With', $dataMapClass, null);

        $traitDefinition = new TraitDefinition(
            new ObjectTypeDefinition($namespace.'\\'.$className)
        );

        $traitDefinition
            ->addClassComment(
                sprintf("Generated Accessor Trait for %s", $fullyQualifiedModelDataMapClass)
            )
            ->addClassComment(
                "This file is only generated if it does not already exist. To regenerate, remove this file."
            )
        ;

        $propertyName = ClassNameTool::classNameToParameterName($dataMapClass);
        $phpTypeEnum = PhpTypeEnum::nullableObjectOfType($fullyQualifiedModelDataMapClass);

        $getterStatementBlock = (new StatementDefinitionCollection())
            ->addStatementDefinition(
                new RawStatementDefinition(
                    \sprintf(
                        "return \$this->%s ?? app(%s::class);",
                        $propertyName,
                        ClassNameTool::globalClassFQN($fullyQualifiedModelDataMapClass)
                    )
                )
            );

        $property = (new ClassPropertyDefinition($propertyName, $phpTypeEnum))
            ->withGetter(
                VisibilityEnum::protectedEnum(),
                InstanceEnum::instanceEnum(),
                $getterStatementBlock
            );

        $traitDefinition->addProperty($property);

        return $traitDefinition;
    }

    /**
     * @param ColumnOwnerInterface $columnOwner
     *
     * @return string
     */
    public function getModelMapAccessorTraitMethodName(ColumnOwnerInterface $columnOwner): string
    {
        return 'get'.$this->getModelDataMapClassGenerator()->getObjectTypeDefinition($columnOwner)->getImportableName();
    }
}