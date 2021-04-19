<?php

namespace Reliese\Generator\Model;

use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\ModelGeneratorConfiguration;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassConstantDefinition;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;

/**
 * Class ModelGenerator
 */
class ModelGenerator
{
    /**
     * @var ModelGeneratorConfiguration
     */
    private ModelGeneratorConfiguration $modelGeneratorConfiguration;

    /**
     * @var MySqlDataTypeMap
     */
    private MySqlDataTypeMap $dataTypeMap;

    /**
     * ModelGenerator constructor.
     *
     * @param ModelGeneratorConfiguration $modelGeneratorConfiguration
     */
    public function __construct(ModelGeneratorConfiguration $modelGeneratorConfiguration)
    {
        $this->modelGeneratorConfiguration = $modelGeneratorConfiguration;
        /*
         * TODO: inject a MySql / Postgress or other DataType mapping as needed
         */
        $this->dataTypeMap = new MySqlDataTypeMap();
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

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getClassNamespace(TableBlueprint $tableBlueprint): string
    {
        return $this->modelGeneratorConfiguration->getNamespace();
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getClassName(TableBlueprint $tableBlueprint): string
    {
        return ClassNameTool::snakeCaseToClassName(
            null,
            $tableBlueprint->getName(),
            $this->modelGeneratorConfiguration->getClassSuffix()
        );
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getAbstractClassName(TableBlueprint $tableBlueprint): string
    {
        return $this->modelGeneratorConfiguration->getParentClassPrefix()
            . $this->getClassName($tableBlueprint);
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getAbstractClassNamespace(TableBlueprint $tableBlueprint): string
    {
        return $this->getClassNamespace($tableBlueprint) .'\\Generated';
    }

    public function fromTableBlueprint(TableBlueprint $tableBlueprint)
    {

        $className = $this->getClassName($tableBlueprint);

        $abstractClassName = $this->getAbstractClassName($tableBlueprint);

        $namespace = $this->getClassNamespace($tableBlueprint);

        $abstractNamespace = $this->getAbstractClassNamespace($tableBlueprint);

        $modelAbstractClassDefinition = new ClassDefinition($abstractClassName, $abstractNamespace);
        $modelAbstractClassDefinition->setParentClass(
            $this->getFullyQualifiedParentClass()
        );

        $modelClassDefinition = new ClassDefinition($className, $namespace);
        $modelClassDefinition->setParentClass(
            $modelAbstractClassDefinition->getFullyQualifiedName()
        );

        foreach ($tableBlueprint->getColumnBlueprints() as $columnBlueprint) {
            $phpTypeEnum = $this->dataTypeMap->getPhpTypeEnumFromDatabaseType(
                $columnBlueprint->getDataType(),
                $columnBlueprint->getMaximumCharacters(),
                $columnBlueprint->getNumericPrecision(),
                $columnBlueprint->getNumericScale(),
                $columnBlueprint->getIsNullable()
            );

            $propertyName = ClassNameTool::columnNameToPropertyName($columnBlueprint->getColumnName());
            $constantName = ClassNameTool::columnNameToConstantName($columnBlueprint->getColumnName());


            $propertyAsConstant = new ClassConstantDefinition(
                $constantName,
                $propertyName
            );

            $modelAbstractClassDefinition->addConstant($propertyAsConstant);
        }

        /*
         * Write the Class Files
         */
        $this->writeClassFiles($modelClassDefinition, $modelAbstractClassDefinition);
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

        $modelClassFolder = $this->modelGeneratorConfiguration->getPath();
        $abstractModelClassFolder = $modelClassFolder . DIRECTORY_SEPARATOR . 'Generated';
        if (!is_dir($modelClassFolder)) {
            mkdir($modelClassFolder, 0777, true);
        }
        if (!is_dir($abstractModelClassFolder)) {
            mkdir($abstractModelClassFolder, 0777, true);
        }

        $dtoFilePath = $modelClassFolder . DIRECTORY_SEPARATOR . $classDefinition->getName() . '.php';
        $abstractDtoFilePath = $abstractModelClassFolder . DIRECTORY_SEPARATOR . $abstractClassDefinition->getName() . '.php';

        if (!file_exists($dtoFilePath)) {
            file_put_contents($dtoFilePath, $dtoClassPhpCode);
        }

        file_put_contents($abstractDtoFilePath, $abstractDtoPhpCode);
    }

    /**
     * @return string
     */
    private function getFullyQualifiedParentClass(): string
    {
        return '\\' . $this->modelGeneratorConfiguration->getParent();
    }
}
