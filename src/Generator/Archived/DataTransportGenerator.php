<?php

namespace Reliese\Generator\DataTransport;

use PhpLibs\ValueState\ValueStateProviderInterface;
use PhpLibs\ValueState\WithValueStateManager;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\ForeignKeyBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\DataTransportObjectGeneratorConfiguration;
use Reliese\Configuration\RelieseConfiguration;
use Reliese\Generator\DataAttribute\DataAttributeGenerator;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Definition\StatementBlockDefinition;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;
use function file_exists;
use function file_put_contents;
use const DIRECTORY_SEPARATOR;

/**
 * Class DataTransportGenerator
 *
 * @deprecated Please use DataTransportObjectGenerator instead
 */
class DataTransportGenerator
{
    /**
     * @var DataAttributeGenerator
     */
    private DataAttributeGenerator $dataAttributeGenerator;

    /**
     * @var DataTransportObjectGeneratorConfiguration
     */
    private DataTransportObjectGeneratorConfiguration $dataTransportGeneratorConfiguration;

    /**
     * @var MySqlDataTypeMap
     */
    private MySqlDataTypeMap $dataTypeMap;

    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    /**
     * @var ClassDefinition[]
     */
    private array $generatedDataTransportObjectClassDefinitions = [];

    /**
     * @var ClassDefinition[]
     */
    private array $generatedAbstractDataTransportObjectClassDefinitions = [];

    /**
     * @var ClassPropertyDefinition[]
     */
    private array $generatedForeignKeyDtoPropertyDefinitions = [];

    /**
     * DataTransportGenerator constructor.
     *
     * @param RelieseConfiguration $relieseConfiguration
     */
    public function __construct(
        RelieseConfiguration $relieseConfiguration
    ) {
        $this->dataTransportGeneratorConfiguration = $relieseConfiguration->getDataTransportGeneratorConfiguration();
        /*
         * TODO: inject a MySql / Postgress or other DataType mapping as needed
         */
        $this->dataTypeMap = new MySqlDataTypeMap();
        $this->dataAttributeGenerator = new DataAttributeGenerator($relieseConfiguration);
    }

    /**
     * @param TableBlueprint $tableBlueprint
     */
    public function fromTableBlueprint(TableBlueprint $tableBlueprint)
    {
        $dtoAbstractClassDefinition = $this->generateAbstractDataTransportObjectClassDefinition($tableBlueprint);
        $dtoClassDefinition = $this->generateDataTransportObjectClassDefinition($tableBlueprint);

        /*
         * Write the Class Files
         */
        $this->writeClassFiles($dtoClassDefinition, $dtoAbstractClassDefinition);
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getFullyQualifiedClassName(ColumnOwnerInterface $tableBlueprint): string
    {
        return $this->getClassNamespace($tableBlueprint).'\\'.$this->getClassName($tableBlueprint);
    }

    public function getClassNamespace(TableBlueprint $tableBlueprint): string
    {
        return $this->dataTransportGeneratorConfiguration->getNamespace();
    }

    public function getClassName(ColumnOwnerInterface $tableBlueprint): string
    {
        return ClassNameTool::snakeCaseToClassName(
            null,
            $tableBlueprint->getName(),
            $this->dataTransportGeneratorConfiguration->getClassSuffix()
        );
    }

    public function generateAbstractDataTransportObjectClassDefinition(TableBlueprint $tableBlueprint): ClassDefinition
    {
        if (\array_key_exists($tableBlueprint->getUniqueName(),
            $this->generatedAbstractDataTransportObjectClassDefinitions)
        ) {
            return $this->generatedAbstractDataTransportObjectClassDefinitions[$tableBlueprint->getUniqueName()];
        }

        $dtoAbstractClassDefinition = new ClassDefinition(
            $this->getAbstractClassName($tableBlueprint),
            $this->getAbstractClassNamespace($tableBlueprint)
        );
        $dtoAbstractClassDefinition->setTableBlueprint($tableBlueprint);

        if ($this->dataTransportGeneratorConfiguration->getUseValueStateTracking()) {
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

        if ($this->dataTransportGeneratorConfiguration->getUseBeforeChangeObservableProperties()) {
            $dtoAbstractClassDefinition->addInterface(
                \PhpLibs\Observable\BeforeValueChangeObservableInterface::class
            );
            $dtoAbstractClassDefinition->addTrait(
                new ClassTraitDefinition(\PhpLibs\Observable\BeforeValueChangeObservableTrait::class)
            );
        }

        if ($this->dataTransportGeneratorConfiguration->getUseAfterChangeObservableProperties()) {
            $dtoAbstractClassDefinition->addInterface(
                \PhpLibs\Observable\AfterValueChangeObservableInterface::class
            );
            $dtoAbstractClassDefinition->addTrait(
                new ClassTraitDefinition(\PhpLibs\Observable\AfterValueChangeObservableTrait::class)
            );
        }

        foreach ($tableBlueprint->getColumnBlueprints() as $columnBlueprint) {

            $propertyName = ClassNameTool::columnNameToPropertyName($columnBlueprint->getColumnName());

            $phpTypeEnum = $this->dataTypeMap->getPhpTypeEnumFromDatabaseType(
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
                    $this->dataTransportGeneratorConfiguration->getUseBeforeChangeObservableProperties()
                )
                ->setIsAfterChangeObservable(
                    $this->dataTransportGeneratorConfiguration->getUseAfterChangeObservableProperties()
                )
                ->withSetter()
                ->withGetter()
            ;

            $dtoAbstractClassDefinition->addProperty($columnClassProperty);
        }

        foreach ($this->getForeignKeyDtoPropertyDefinitions($tableBlueprint) as $fkDtoProperty) {
            $dtoAbstractClassDefinition->addProperty($fkDtoProperty);
        }

        return $dtoAbstractClassDefinition;
    }

    public function generateDataTransportObjectClassDefinition(TableBlueprint $tableBlueprint): ClassDefinition
    {
        if (\array_key_exists($tableBlueprint->getUniqueName(), $this->generatedDataTransportObjectClassDefinitions)) {
            return $this->generatedDataTransportObjectClassDefinitions[$tableBlueprint->getUniqueName()];
        }

        $dtoClassDefinition = new ClassDefinition(
            $this->getClassName($tableBlueprint),
            $this->getClassNamespace($tableBlueprint)
        );
        $dtoClassDefinition
            ->setTableBlueprint($tableBlueprint)
            ->setParentClass(
                $this->generateAbstractDataTransportObjectClassDefinition($tableBlueprint)->getFullyQualifiedName()
            )
        ;

        return $this->generatedDataTransportObjectClassDefinitions[$tableBlueprint->getUniqueName()]
            = $dtoClassDefinition;
    }

    private function getAbstractClassName(TableBlueprint $tableBlueprint): string
    {
        return $this->dataTransportGeneratorConfiguration->getParentClassPrefix()
            . $this->getClassName($tableBlueprint);
    }

    private function getAbstractClassNamespace(TableBlueprint $tableBlueprint): string
    {
        return $this->getClassNamespace($tableBlueprint) .'\\Generated';
    }

    /**
     * @param ClassDefinition $classDefinition
     * @param ClassDefinition $abstractClassDefinition
     */
    private function writeClassFiles(
        ClassDefinition $classDefinition,
        ClassDefinition $abstractClassDefinition,
    ): void
    {
        $classFormatter = new ClassFormatter();

        $dtoClassPhpCode = $classFormatter->format($classDefinition);
        $abstractDtoPhpCode = $classFormatter->format($abstractClassDefinition);

        $dtoClassFolder = $this->dataTransportGeneratorConfiguration->getPath();
        $abstractDtoClassFolder = $dtoClassFolder . DIRECTORY_SEPARATOR . 'Generated';
        if (!is_dir($dtoClassFolder)) {
            \mkdir($dtoClassFolder, 0755, true);
        }
        if (!is_dir($abstractDtoClassFolder)) {
            \mkdir($abstractDtoClassFolder, 0755, true);
        }

        $dtoFilePath = $dtoClassFolder . DIRECTORY_SEPARATOR . $classDefinition->getClassName() . '.php';
        $abstractDtoFilePath = $abstractDtoClassFolder . DIRECTORY_SEPARATOR . $abstractClassDefinition->getClassName() . '.php';

        if (!file_exists($dtoFilePath)) {
            file_put_contents($dtoFilePath, $dtoClassPhpCode);
        }

        file_put_contents($abstractDtoFilePath, $abstractDtoPhpCode);
    }

    public function generateForeignKeyDtoPropertyDefinition(
        TableBlueprint $tableBlueprint,
        ForeignKeyBlueprint $foreignKeyBlueprint
    ) : ClassPropertyDefinition {

        if (\array_key_exists($foreignKeyBlueprint->getName(), $this->generatedForeignKeyDtoPropertyDefinitions)) {
            return $this->generatedForeignKeyDtoPropertyDefinitions[$foreignKeyBlueprint->getName()];
        }

        $fkDtoProperty = null;
        $dtoVariableName = null;

        $referencedTableBlueprint = $foreignKeyBlueprint->getReferencedTableBlueprint();

        $fkDtoClassName = $this->getClassName($referencedTableBlueprint);

        $dtoVariableName = ClassNameTool::dtoClassNameToVariableName($fkDtoClassName);

        $fkDtoProperty = new ClassPropertyDefinition(
            $dtoVariableName,
            PhpTypeEnum::nullableObjectOfType($this->getFullyQualifiedClassName($referencedTableBlueprint))
        );

        $fkDtoProperty
            ->setForeignKeyBlueprint($foreignKeyBlueprint)
            ->setIsBeforeChangeObservable($this->dataTransportGeneratorConfiguration->getUseBeforeChangeObservableProperties())
            ->setIsAfterChangeObservable($this->dataTransportGeneratorConfiguration->getUseBeforeChangeObservableProperties())
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

    /**
     * @param TableBlueprint $tableBlueprint
     * TODO: figure out how to cache these so they are not rebuilt on every call
     * @return ClassPropertyDefinition[]
     */
    public function getForeignKeyDtoPropertyDefinitions(TableBlueprint $tableBlueprint): array
    {
        $results = [];

        if (empty($tableBlueprint->getForeignKeyBlueprints())) {
            return $results;
        }

        foreach ($tableBlueprint->getForeignKeyBlueprints() as $foreignKeyBlueprint) {
            $fkDtoProperty = $this->generateForeignKeyDtoPropertyDefinition($tableBlueprint, $foreignKeyBlueprint);
            $results[] = $fkDtoProperty;
        }
        return $results;

        /**
         * Examine FKs
         * @var ForeignKeyBlueprint $foreignKeyBlueprint
         */
        foreach ($tableBlueprint->getForeignKeyBlueprintsGroupedByReferencedTable() as
            $referencedTableName => $foreignKeyBlueprints
        ) {
            $commonColumns = [];
            $fkDtoProperty = null;
            $dtoVariableName = null;

            $referencedTableBlueprint = null;
            foreach ($foreignKeyBlueprints as $foreignKeyName =>  $foreignKeyBlueprint) {

                $referencedTableBlueprint ??= $foreignKeyBlueprint->getReferencedTableBlueprint();

                $fkDtoClassName = $this->getClassName($referencedTableBlueprint);

                $dtoVariableName = ClassNameTool::dtoClassNameToVariableName($fkDtoClassName);
                //
                if (\is_null($fkDtoProperty)) {
                    $fkDtoProperty = (
                    new ClassPropertyDefinition(
                        $dtoVariableName,
                        PhpTypeEnum::nullableObjectOfType(
                            $this->getFullyQualifiedClassName($referencedTableBlueprint)
                        )
                    )
                    )
                        ->setIsBeforeChangeObservable($this->dataTransportGeneratorConfiguration->getUseBeforeChangeObservableProperties())
                        ->setIsAfterChangeObservable($this->dataTransportGeneratorConfiguration->getUseBeforeChangeObservableProperties())
                        ->withSetter()
                        ->withGetter()
                    ;
                }

                foreach ($foreignKeyBlueprint->getFkColumnPairs() as $columns) {
                    /**
                     * @var ColumnBlueprint $referencingColumn
                     * @var ColumnBlueprint $referencedColumn
                     */
                    [$referencingColumn, $referencedColumn] = $columns;

                    $commonColumns[$referencingColumn->getColumnName().' = '.$referencedColumn->getColumnName()] =
                        [$referencingColumn, $referencedColumn];
                }
            }

            foreach ($commonColumns as $columnPairs) {
                [$referencingColumn, $referencedColumn] = $columnPairs;

                $fkDtoProperty->addAdditionalSetterOperation(
                    new RawStatementDefinition(
                        \sprintf(
                            "\$this->%s(\$%s->%s());",
                            ClassNameTool::columnNameToSetterName($referencingColumn->getColumnName()),
                            $dtoVariableName,
                            ClassNameTool::columnNameToGetterName($referencedColumn->getColumnName()),
                        )
                    )
                );
            }
            $results[] = $fkDtoProperty;
        }
        return $results;
    }
}

