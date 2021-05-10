<?php

namespace Reliese\Generator\DataAccess;

use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\DataAccessGeneratorConfiguration;
use Reliese\Generator\DataAttribute\DataAttributeGenerator;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;
use const DIRECTORY_SEPARATOR;

/**
 * Class DataAccessGenerator
 */
class DataAccessGenerator
{
    /**
     * @var DataAttributeGenerator
     */
    private DataAttributeGenerator $dataAttributeGenerator;

    /**
     * @var DataAccessGeneratorConfiguration
     */
    private DataAccessGeneratorConfiguration $dataAccessGeneratorConfiguration;

    /**
     * @var MySqlDataTypeMap
     */
    private MySqlDataTypeMap $dataTypeMap;

    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    /**
     * DataAccessGenerator constructor.
     *
     * @param DataAccessGeneratorConfiguration $dataAccessGeneratorConfiguration
     */
    public function __construct(
        DataAccessGeneratorConfiguration $dataAccessGeneratorConfiguration
    ) {
        $this->dataAccessGeneratorConfiguration = $dataAccessGeneratorConfiguration;
        /*
         * TODO: inject a MySql / Postgress or other DataType mapping as needed
         */
        $this->dataTypeMap = new MySqlDataTypeMap();
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

        /*
         * TODO: Add generic methods like "get by id"
         */

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
        return $this->dataAccessGeneratorConfiguration->getNamespace();
    }

    public function getClassName(TableBlueprint $tableBlueprint): string
    {
        return ClassNameTool::snakeCaseToClassName(
            $this->dataAccessGeneratorConfiguration->getClassPrefix(),
            $tableBlueprint->getName(),
            $this->dataAccessGeneratorConfiguration->getClassSuffix()
        );
    }

    private function getAbstractClassName(TableBlueprint $tableBlueprint): string
    {
        return $this->dataAccessGeneratorConfiguration->getParentClassPrefix()
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

        $dtoClassFolder = $this->dataAccessGeneratorConfiguration->getPath();
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
