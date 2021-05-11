<?php

namespace Reliese\Generator\DataTransport;

use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\ForeignKeyBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\DataTransportObjectGeneratorConfiguration;
use Reliese\Generator\DataAttribute\DataAttributeGenerator;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;
use function file_exists;
use function file_put_contents;
use function mkdir;
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
     * DataTransportGenerator constructor.
     *
     * @param DataTransportObjectGeneratorConfiguration $dataTransportGeneratorConfiguration
     * @param DataAttributeGenerator $dataAttributeGenerator
     */
    public function __construct(
        DataTransportObjectGeneratorConfiguration $dataTransportGeneratorConfiguration,
        DataAttributeGenerator $dataAttributeGenerator
    ) {
        $this->dataTransportGeneratorConfiguration = $dataTransportGeneratorConfiguration;
        /*
         * TODO: inject a MySql / Postgress or other DataType mapping as needed
         */
        $this->dataTypeMap = new MySqlDataTypeMap();
        $this->dataAttributeGenerator = $dataAttributeGenerator;
    }

    /**
     * @param TableBlueprint $tableBlueprint
     */
    public function fromTableBlueprint(TableBlueprint $tableBlueprint)
    {
        $className = $this->getClassName($tableBlueprint);

        $abstractClassName = $this->getAbstractClassName($tableBlueprint);

        $namespace = $this->getClassNamespace($tableBlueprint);

        $abstractNamespace = $this->getAbstractClassNamespace($tableBlueprint);

        $dtoAbstractClassDefinition = new ClassDefinition($abstractClassName, $abstractNamespace);

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
        
        $dtoClassDefinition = new ClassDefinition($className, $namespace);
        $dtoClassDefinition->setParentClass($dtoAbstractClassDefinition->getFullyQualifiedName());

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
                            "\$this->%s(\$%s->%s());\n",
                            ClassNameTool::columnNameToSetterName($referencingColumn->getColumnName()),
                            $dtoVariableName,
                            ClassNameTool::columnNameToGetterName($referencedColumn->getColumnName()),
                        )
                    )
                );
            }
            $dtoAbstractClassDefinition->addProperty($fkDtoProperty);
        }

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
    public function getFullyQualifiedClassName(TableBlueprint $tableBlueprint): string
    {
        return $this->getClassNamespace($tableBlueprint).'\\'.$this->getClassName($tableBlueprint);
    }

    public function getClassNamespace(TableBlueprint $tableBlueprint): string
    {
        return $this->dataTransportGeneratorConfiguration->getNamespace();
    }

    public function getClassName(TableBlueprint $tableBlueprint): string
    {
        return ClassNameTool::snakeCaseToClassName(
            null,
            $tableBlueprint->getName(),
            $this->dataTransportGeneratorConfiguration->getClassSuffix()
        );
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
}

