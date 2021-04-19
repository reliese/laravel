<?php

namespace Reliese\Generator\DataTransport;

use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\DataTransportObjectGeneratorConfiguration;
use Reliese\Generator\DataAttribute\DataAttributeGenerator;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;
use const DIRECTORY_SEPARATOR;

/**
 * Class DataTransportGenerator
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
    public function fromTableBlueprint(
        TableBlueprint $tableBlueprint
    ) {

        $className = $this->getClassName($tableBlueprint);

        $abstractClassName = $this->getAbstractClassName($tableBlueprint);

        $namespace = $this->getClassNamespace($tableBlueprint);

        $abstractNamespace = $this->getAbstractClassNamespace($tableBlueprint);

        $dtoAbstractClassDefinition = new ClassDefinition($abstractClassName, $abstractNamespace);

        $dtoClassDefinition = new ClassDefinition($className, $namespace);
        $dtoClassDefinition->setParentClass($dtoAbstractClassDefinition->getFullyQualifiedName());

        foreach ($tableBlueprint->getColumnBlueprints() as $columnBlueprint) {

            $propertyName = ClassNameTool::columnNameToPropertyName($columnBlueprint->getColumnName());

            $phpTypeEnum = $this->dataTypeMap->getPhpTypeEnumFromDatabaseType(
                $columnBlueprint->getDataType(),
                $columnBlueprint->getMaximumCharacters(),
                $columnBlueprint->getNumericPrecision(),
                $columnBlueprint->getNumericScale(),
                $columnBlueprint->getIsNullable()
            );

            $traitFullyQualifiedName = $this->dataAttributeGenerator->getFullyQualifiedTraitName($tableBlueprint,
                $columnBlueprint
            );

            echo "looking for: $traitFullyQualifiedName\n";

            if (\class_exists($traitFullyQualifiedName)) {
                /*
                 * If a DataAttribute trait has been defined for this table and column, then use it here
                 */
                $classTraitDefinition =  new ClassTraitDefinition(
                    $this->dataAttributeGenerator->getTraitName($tableBlueprint, $columnBlueprint),
                    $this->dataAttributeGenerator->getTraitNamespace($tableBlueprint, $columnBlueprint))
                ;
                $dtoAbstractClassDefinition->addTrait($classTraitDefinition);
            } else {
                /*
                 * Use a property defined directly on the class
                 */
                $columnClassProperty = (new ClassPropertyDefinition($propertyName, $phpTypeEnum))->withSetter()
                                                                                                 ->withGetter()
                ;

                $dtoAbstractClassDefinition->addProperty($columnClassProperty);
            }
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
        //        echo "\n---Class---\n$dtoClassPhpCode\n\n\n---Base Class---\n$abstractDtoPhpCode\n\n";

        $dtoClassFolder = $this->dataTransportGeneratorConfiguration->getPath();
        $abstractDtoClassFolder = $dtoClassFolder . DIRECTORY_SEPARATOR . 'Generated';
        if (!is_dir($dtoClassFolder)) {
            \mkdir($dtoClassFolder, 0755, true);
        }
        if (!is_dir($abstractDtoClassFolder)) {
            \mkdir($abstractDtoClassFolder, 0755, true);
        }

        $dtoFilePath = $dtoClassFolder . DIRECTORY_SEPARATOR . $classDefinition->getName() . '.php';
        $abstractDtoFilePath = $abstractDtoClassFolder . DIRECTORY_SEPARATOR . $abstractClassDefinition->getName() . '.php';

        if (!\file_exists($dtoFilePath)) {
            \file_put_contents($dtoFilePath, $dtoClassPhpCode);
        }
        \file_put_contents($abstractDtoFilePath, $abstractDtoPhpCode);
    }
}
