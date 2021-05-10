<?php

namespace Reliese\Generator\DataMap;

use Illuminate\Support\Str;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\ModelDataMapGeneratorConfiguration;
use Reliese\Generator\DataTransport\DataTransportGenerator;
use Reliese\Generator\Model\ModelGenerator;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
use Reliese\MetaCode\Definition\FunctionParameterDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;
use function file_exists;
use function file_put_contents;
use function mkdir;
use const DIRECTORY_SEPARATOR;

/**
 * Class ModelDataMapGenerator
 */
class ModelDataMapGenerator
{
    /**
     * @var DataTransportGenerator
     */
    private DataTransportGenerator $dataTransportGenerator;

    /**
     * @var ModelDataMapGeneratorConfiguration
     */
    private ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration;

    /**
     * @var MySqlDataTypeMap
     */
    private MySqlDataTypeMap $dataTypeMap;

    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    /**
     * @var ModelGenerator
     */
    private ModelGenerator $modelGenerator;

    /**
     * ModelDataMapGenerator constructor.
     *
     * @param ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration
     * @param ModelGenerator $modelGenerator
     * @param DataTransportGenerator $dataTransportGenerator
     */
    public function __construct(
        ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration,
        ModelGenerator $modelGenerator,
        DataTransportGenerator $dataTransportGenerator
    ) {
        $this->modelDataMapGeneratorConfiguration = $modelDataMapGeneratorConfiguration;
        /*
         * TODO: inject a MySql / Postgress or other DataType mapping as needed
         */
        $this->dataTypeMap = new MySqlDataTypeMap();

        $this->modelGenerator = $modelGenerator;
        $this->dataTransportGenerator = $dataTransportGenerator;
    }

    /**
     * @param TableBlueprint $tableBlueprint
     */
    public function fromTableBlueprint(TableBlueprint $tableBlueprint)
    {
        $className = ClassNameTool::snakeCaseToClassName(
            null,
            $tableBlueprint->getName(),
            $this->modelDataMapGeneratorConfiguration->getClassSuffix()
        );

//        $abstractClassName = $this->modelDataMapGeneratorConfiguration->getParentClassPrefix().$className;
//        $namespace = $this->modelDataMapGeneratorConfiguration->getNamespace();
//        $abstractNamespace = $namespace .'\\Generated';
//
//        $abstractClassDefinition = new ClassDefinition($abstractClassName, $abstractNamespace);
//
//        $classDefinition = new ClassDefinition($className, $namespace);
//        $classDefinition->setParentClass($abstractClassDefinition->getFullyQualifiedName());

        /*
         * Determine the model class name and namespace
         */
        $modelClassName = $this->modelGenerator->getClassName($tableBlueprint);
        $modelFullyQualifiedClassName = $this->modelGenerator->getFullyQualifiedClassName($tableBlueprint);
        $modelParameterName = ClassNameTool::classNameToParameterName($modelClassName);
        $modelParameterType = PhpTypeEnum::objectOfType($modelFullyQualifiedClassName);

        /*
         * Determine the dto Class name and namespace
         */
        $dtoClassName = $this->dataTransportGenerator->getClassName($tableBlueprint);
        $dtoFullyQualifiedClassName = $this->dataTransportGenerator->getFullyQualifiedClassName($tableBlueprint);
        $dtoParameterName = ClassNameTool::classNameToParameterName($dtoClassName);
        $dtoParameterType = PhpTypeEnum::objectOfType($dtoFullyQualifiedClassName);

        /*
         * Determine the Map class name and namespace
         */
        $modelMapClassName = $this->getClassName($tableBlueprint);
        $modelMapClassNamespace = $this->getClassNamespace($tableBlueprint);
        $abstractModelMapClassName = $this->getAbstractClassName($tableBlueprint);
        $abstractModelMapClassNamespace = $this->getAbstractClassNamespace($tableBlueprint);

        /*
         * Define the abstract map class
         */
        $modelMapAbstractClassDefinition = new ClassDefinition($abstractModelMapClassName, $abstractModelMapClassNamespace);
        $modelMapClassDefinition = new ClassDefinition($modelMapClassName, $modelMapClassNamespace);
        $modelMapClassDefinition->setParentClass($modelMapAbstractClassDefinition->getFullyQualifiedName());

        /*
         * Build the model to dto mapping function
         */
        $mapFromDtoFunctionName = 'to'.$dtoClassName;
        $functionParameterDefinitions = [
            new FunctionParameterDefinition($modelParameterName, $modelParameterType),
            new FunctionParameterDefinition($dtoParameterName, $dtoParameterType,true)
        ];

        $mapToDtoMethodDefinition = new ClassMethodDefinition(
            $mapFromDtoFunctionName,
            PhpTypeEnum::boolType(),
            $functionParameterDefinitions,
        );

        foreach ($tableBlueprint->getColumnBlueprints() as $columnBlueprint) {
            $modelConstant = '\\'.$modelParameterType->toDeclarationType().'::'.Str::upper($columnBlueprint->getColumnName());
            $setterName = ClassNameTool::columnNameToSetterName($columnBlueprint->getColumnName());
            $dtoPropertyAssignmentStatement = "\${$dtoParameterName}->{$setterName}(\${$modelParameterName}[{$modelConstant}]);";
            $mapToDtoMethodDefinition->appendBodyStatement(new RawStatementDefinition($dtoPropertyAssignmentStatement));
//            dd($dtoPropertyAssignmentStatement);
//            dd([$modelParameterName, $modelConstant, $dtoParameterName, $setterName]);
        }
        $mapToDtoMethodDefinition->appendBodyStatement(new RawStatementDefinition("return true;"));

        $modelMapAbstractClassDefinition->addMethodDefinition($mapToDtoMethodDefinition);

        /*
         * Build the dto to model mapping function
         */
        $mapFromDtoFunctionName = 'from'.$dtoClassName;

        $mapToDtoMethodDefinition = new ClassMethodDefinition(
            $mapFromDtoFunctionName,
            PhpTypeEnum::boolType(),
            $functionParameterDefinitions,
        );

        foreach ($tableBlueprint->getColumnBlueprints() as $columnBlueprint) {
            $modelConstant = '\\'.$modelParameterType->toDeclarationType().'::'.Str::upper($columnBlueprint->getColumnName());
            $getterName = ClassNameTool::columnNameToGetterName($columnBlueprint->getColumnName());
            $dtoPropertyAssignmentStatement = "\${$modelParameterName}[{$modelConstant}] = \${$dtoParameterName}->{$getterName}();";
            $mapToDtoMethodDefinition->appendBodyStatement(new RawStatementDefinition($dtoPropertyAssignmentStatement));
            //            dd($dtoPropertyAssignmentStatement);
            //            dd([$modelParameterName, $modelConstant, $dtoParameterName, $setterName]);
        }
        $mapToDtoMethodDefinition->appendBodyStatement(new RawStatementDefinition("return true;"));

        $modelMapAbstractClassDefinition->addMethodDefinition($mapToDtoMethodDefinition);

        /*
         * Write the Class Files
         */
        $this->writeClassFiles($modelMapClassDefinition, $modelMapAbstractClassDefinition);
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
        return $this->modelDataMapGeneratorConfiguration->getNamespace();
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
            $this->modelDataMapGeneratorConfiguration->getClassSuffix()
        );
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getAbstractClassName(TableBlueprint $tableBlueprint): string
    {
        return $this->modelDataMapGeneratorConfiguration->getParentClassPrefix()
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
     * @param ClassDefinition $classDefinition
     * @param ClassDefinition $abstractClassDefinition
     */
    private function writeClassFiles(
        ClassDefinition $classDefinition,
        ClassDefinition $abstractClassDefinition,
    ): void
    {
        $classFormatter = new ClassFormatter();

        $classPhpCode = $classFormatter->format($classDefinition);
        $abstractPhpCode = $classFormatter->format($abstractClassDefinition);

        $classFolder = $this->modelDataMapGeneratorConfiguration->getPath();
        $abstractClassFolder = $classFolder . DIRECTORY_SEPARATOR . 'Generated';
        if (!is_dir($classFolder)) {
            \mkdir($classFolder, 0755, true);
        }
        if (!is_dir($abstractClassFolder)) {
            \mkdir($abstractClassFolder, 0755, true);
        }

        $classFilePath = $classFolder . DIRECTORY_SEPARATOR . $classDefinition->getClassName() . '.php';
        $abstractFilePath = $abstractClassFolder . DIRECTORY_SEPARATOR . $abstractClassDefinition->getClassName() . '.php';

        if (!file_exists($classFilePath)) {
            file_put_contents($classFilePath, $classPhpCode);
        }
        file_put_contents($abstractFilePath, $abstractPhpCode);
    }
}
