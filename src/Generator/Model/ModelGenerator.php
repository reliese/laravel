<?php

namespace Reliese\Generator\Model;

use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\ModelGeneratorConfiguration;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassConstantDefinition;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Tool\ClassNameTool;

/**
 * Class ModelGenerator
 */
class ModelGenerator
{
    const PROPERTY_TABLE = 'table';

    private MySqlDataTypeMap $dataTypeMap;

    /**
     * @var ModelGeneratorConfiguration
     */
    private ModelGeneratorConfiguration $modelGeneratorConfiguration;

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

    public function generateModelClass(TableBlueprint $tableBlueprint): ClassDefinition
    {
        $className = $this->getClassName($tableBlueprint);

        $namespace = $this->getClassNamespace($tableBlueprint);

        $modelClassDefinition = new ClassDefinition($className, $namespace);

        $modelClassDefinition
            ->setParentClass($this->getFullyQualifiedAbstractClass())
            ->setDirectory($this->getClassDirectory())
            ->setFilePath($this->getClassFilePath($tableBlueprint))
        ;

        return $modelClassDefinition;
    }

    public function generateModelAbstractClass(TableBlueprint $tableBlueprint): ClassDefinition
    {
        $abstractClassName = $this->getAbstractClassName($tableBlueprint);

        $abstractNamespace = $this->getAbstractClassNamespace($tableBlueprint);

        $modelAbstractClassDefinition = new ClassDefinition($abstractClassName, $abstractNamespace);

        $modelAbstractClassDefinition
            ->setParentClass($this->getFullyQualifiedAbstractClass())
            ->setDirectory($this->getAbstractClassDirectory())
            ->setFilePath($this->getAbstractClassFilePath($tableBlueprint))
            ->addTraits($this->generateTraits($tableBlueprint))
            ->addConstants($this->generateColumnConstants($tableBlueprint))
            ->addProperties($this->generateProperties($tableBlueprint))
        ;

        return $modelAbstractClassDefinition;
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

    /**
     * @return string
     */
    private function getFullyQualifiedAbstractClass(): string
    {
        return '\\' . $this->modelGeneratorConfiguration->getParent();
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return ClassConstantDefinition[]
     */
    private function generateColumnConstants(TableBlueprint $tableBlueprint): array
    {
        $constants = [];

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

            $constants[] = $propertyAsConstant;
        }

        return $constants;
    }

    public function getClassDirectory(): string
    {
        return $this->modelGeneratorConfiguration->getPath();
    }

    /**
     * @return string
     */
    private function getAbstractClassDirectory(): string
    {
        return $this->getClassDirectory() . DIRECTORY_SEPARATOR . 'Generated';
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    private function getClassFilePath(TableBlueprint $tableBlueprint): string
    {
        return $this->getClassDirectory() . DIRECTORY_SEPARATOR . $this->getClassName($tableBlueprint) . '.php';
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    private function getAbstractClassFilePath(TableBlueprint $tableBlueprint): string
    {
        return $this->getAbstractClassDirectory() . DIRECTORY_SEPARATOR . $this->getAbstractClassName($tableBlueprint) . '.php';
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return ClassPropertyDefinition[]
     */
    private function generateProperties(TableBlueprint $tableBlueprint): array
    {
        $properties = [];

        if ($this->shouldAddTableProperty()) {
            $properties[] = $this->generateTableProperty($tableBlueprint);
        }

        return $properties;
    }

    /**
     * @return bool
     */
    private function shouldAddTableProperty(): bool
    {
        return $this->modelGeneratorConfiguration->hasClassSuffix();
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return ClassPropertyDefinition
     */
    private function generateTableProperty(TableBlueprint $tableBlueprint): ClassPropertyDefinition
    {
        $property = new ClassPropertyDefinition(
            static::PROPERTY_TABLE,
            PhpTypeEnum::stringType(),
            VisibilityEnum::protectedEnum()
        );

        $property->setValue($tableBlueprint->getName());

        return $property;
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return ClassTraitDefinition[]
     */
    private function generateTraits(TableBlueprint $tableBlueprint): array
    {
        $traitDefinitions = [];

        foreach ($this->modelGeneratorConfiguration->getTraits() as $trait) {
            $traitDefinitions[] = new ClassTraitDefinition($trait);
        }

        return $traitDefinitions;
    }
}
