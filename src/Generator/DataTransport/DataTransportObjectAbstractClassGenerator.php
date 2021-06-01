<?php

namespace Reliese\Generator\DataTransport;

use PhpLibs\ValueState\ValueStateProviderInterface;
use PhpLibs\ValueState\WithValueStateManager;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Blueprint\ForeignKeyBlueprint;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Database\WithPhpTypeMap;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Definition\StatementBlockDefinition;
use Reliese\MetaCode\Enum\AbstractEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Tool\ClassNameTool;

/**
 * Class DataTransportObjectAbstractClassGenerator
 */
class DataTransportObjectAbstractClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;
    use WithPhpTypeMap;
    use WithDataTransportObjectClassGenerator;

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
        return $this->getConfigurationProfile()->getDataTransportObjectGeneratorConfiguration()
            ->getGeneratedClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getDataTransportObjectGeneratorConfiguration()
            ->getGeneratedClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getDataTransportObjectGeneratorConfiguration()
            ->getGeneratedClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $dtoAbstractClassDefinition = new ClassDefinition(
            $this->getObjectTypeDefinition($columnOwner),
            AbstractEnum::abstractEnum()
        );
        $dtoAbstractClassDefinition->setOriginatingBlueprint($columnOwner);

        $dataTransportObjectGeneratorConfiguration = $this->getConfigurationProfile()
            ->getDataTransportObjectGeneratorConfiguration();

        if ($dataTransportObjectGeneratorConfiguration->getUseValueStateTracking()) {
            /*
             * Add interface:
             *  ValueStateProviderInterface
             * include:
             *   use WithValueStateManager;
             * And bind the tracking in the constructor:
             *   $this->bindValueChangeStateTracking();
             */
            $dtoAbstractClassDefinition
                ->addInterface(ValueStateProviderInterface::class)
                ->addTrait(new ClassTraitDefinition(WithValueStateManager::class))
                ->addConstructorStatement(new RawStatementDefinition("\$this->bindValueChangeStateTracking();"))
            ;
        }

        if ($dataTransportObjectGeneratorConfiguration->getUseBeforeChangeObservableProperties()) {
            $dtoAbstractClassDefinition->addInterface(
                \PhpLibs\Observable\BeforeValueChangeObservableInterface::class
            );
            $dtoAbstractClassDefinition->addTrait(
                new ClassTraitDefinition(\PhpLibs\Observable\BeforeValueChangeObservableTrait::class)
            );
        }

        if ($dataTransportObjectGeneratorConfiguration->getUseAfterChangeObservableProperties()) {
            $dtoAbstractClassDefinition->addInterface(
                \PhpLibs\Observable\AfterValueChangeObservableInterface::class
            );
            $dtoAbstractClassDefinition->addTrait(
                new ClassTraitDefinition(\PhpLibs\Observable\AfterValueChangeObservableTrait::class)
            );
        }

        foreach ($columnOwner->getColumnBlueprints() as $columnBlueprint) {

            $propertyName = ClassNameTool::columnNameToPropertyName($columnBlueprint->getColumnName());

            $phpTypeEnum = $this->getPhpTypeMapping()->getPhpTypeEnumFromDatabaseType(
                $columnBlueprint->getDataType(),
                $columnBlueprint->getMaximumCharacters(),
                $columnBlueprint->getNumericPrecision(),
                $columnBlueprint->getNumericScale(),
                // This value must always be true in order to allow for partial DTOs.
                // Otherwise and error is raised when attempting to read a property that has not been assigned a value
                true //$columnBlueprint->getIsNullable()
            );

            /*
             * Use a property defined directly on the class
             */
            $columnClassProperty = (new ClassPropertyDefinition($propertyName, $phpTypeEnum))
                ->setColumnBlueprint($columnBlueprint)
                ->setIsBeforeChangeObservable(
                    $dataTransportObjectGeneratorConfiguration->getUseBeforeChangeObservableProperties()
                )
                ->setIsAfterChangeObservable(
                    $dataTransportObjectGeneratorConfiguration->getUseAfterChangeObservableProperties()
                )
                ->withSetter()
                ->withGetter()
            ;

            $dtoAbstractClassDefinition->addProperty($columnClassProperty);
        }

        foreach ($this->getForeignKeyDtoPropertyDefinitions($columnOwner) as $fkDtoProperty) {
            $dtoAbstractClassDefinition->addProperty($fkDtoProperty);
        }

        return $dtoAbstractClassDefinition;
    }

    /**
     * @param ColumnOwnerInterface $columnOwner
     * TODO: figure out how to cache these so they are not rebuilt on every call
     *
     * @return ClassPropertyDefinition[]
     */
    public function getForeignKeyDtoPropertyDefinitions(ColumnOwnerInterface $columnOwner): array
    {
        $results = [];

        if (empty($columnOwner->getForeignKeyBlueprints())) {
            return $results;
        }

        foreach ($columnOwner->getForeignKeyBlueprints() as $foreignKeyBlueprint) {
            $fkDtoProperty = $this->generateForeignKeyDtoPropertyDefinition($columnOwner, $foreignKeyBlueprint);
            $results[] = $fkDtoProperty;
        }
        return $results;
    }

    public function generateForeignKeyDtoPropertyDefinition(
        ColumnOwnerInterface $columnOwner,
        ForeignKeyBlueprint $foreignKeyBlueprint
    ) : ClassPropertyDefinition {

        if (\array_key_exists($foreignKeyBlueprint->getName(), $this->generatedForeignKeyDtoPropertyDefinitions)) {
            return $this->generatedForeignKeyDtoPropertyDefinitions[$foreignKeyBlueprint->getName()];
        }

        $dataTransportGeneratorConfiguration = $this->getConfigurationProfile()
            ->getDataTransportObjectGeneratorConfiguration();
        $fkDtoProperty = null;
        $dtoVariableName = null;

        $referencedTableBlueprint = $foreignKeyBlueprint->getReferencedTableBlueprint();

        $referencedClassDefinition = $this->getDataTransportObjectClassGenerator()
            ->getClassDefinition($referencedTableBlueprint);

        $fkDtoClassName = $referencedClassDefinition->getClassName();

        $dtoVariableName = ClassNameTool::classNameToVariableName($fkDtoClassName);

        $fkDtoProperty = new ClassPropertyDefinition(
            $dtoVariableName,
            PhpTypeEnum::nullableObjectOfType($referencedClassDefinition->getFullyQualifiedName())
        );

        $fkDtoProperty
            ->setForeignKeyBlueprint($foreignKeyBlueprint)
            ->setIsBeforeChangeObservable($dataTransportGeneratorConfiguration->getUseBeforeChangeObservableProperties())
            ->setIsAfterChangeObservable($dataTransportGeneratorConfiguration->getUseBeforeChangeObservableProperties())
            ->withSetter()
            ->withGetter()
        ;

        $commonColumns = [];
        /*
         * Track which columns should be updated when this property is set
         */
        foreach ($foreignKeyBlueprint->getFkColumnPairs() as $columns) {
            /**
             * @var ColumnBlueprint $referencingColumn
             * @var ColumnBlueprint $referencedColumn
             */
            [$referencingColumn, $referencedColumn] = $columns;

            $commonColumns[$referencingColumn->getColumnName().' = '.$referencedColumn->getColumnName()] =
                [$referencingColumn, $referencedColumn];
        }

        foreach ($commonColumns as $columnPairs) {
            [$referencingColumn, $referencedColumn] = $columnPairs;

            $propertyConstant = ClassPropertyDefinition::getPropertyNameConstantName(
                ClassNameTool::columnNameToPropertyName($referencedColumn->getColumnName())
            );

            $propertyGetterCall = \sprintf(
                "\$%s->%s()",
                $dtoVariableName,
                ClassNameTool::columnNameToGetterName($referencedColumn->getColumnName()),
            );

            /*
             * If the referenced value was initialized, then set the fk value to match
             */
            $ifInitialized = new RawStatementDefinition(
                sprintf(
                    "if (\$%s->getValueWasInitialized(%s::%s) && !empty(%s))",
                    $dtoVariableName,
                    $fkDtoClassName,
                    $propertyConstant,
                    $propertyGetterCall
                )
            );

            $setFkFieldIfInitialized = new StatementBlockDefinition($ifInitialized);
            $setFkFieldIfInitialized->addStatementDefinition(
                new RawStatementDefinition(
                    \sprintf(
                        "\$this->%s(%s);",
                        ClassNameTool::columnNameToSetterName($referencingColumn->getColumnName()),
                        $propertyGetterCall,
                    )
                )
            );

            $fkDtoProperty->addAdditionalSetterOperation($setFkFieldIfInitialized);
        }
        return $fkDtoProperty;
    }
}