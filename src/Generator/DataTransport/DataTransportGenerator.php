<?php

namespace Reliese\Generator\DataTransport;

use Illuminate\Support\Str;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\DataTransportGenerationConfiguration;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;
use const DIRECTORY_SEPARATOR;

/**
 * Class DataTransportGenerator
 */
class DataTransportGenerator
{
    /**
     * @var DataTransportGenerationConfiguration
     */
    private DataTransportGenerationConfiguration $dataTransportGenerationConfiguration;

    /**
     * @var MySqlDataTypeMap
     */
    private MySqlDataTypeMap $dataTypeMap;

    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    public function __construct(
        DataTransportGenerationConfiguration $dataTransportGenerationConfiguration
    ) {
        $this->dataTransportGenerationConfiguration = $dataTransportGenerationConfiguration;
        /*
         * TODO: inject a MySql / Postgress or other DataType mapping as needed
         */
        $this->dataTypeMap = new MySqlDataTypeMap();
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return ClassDefinition
     */
    public function fromTableBlueprint(
        TableBlueprint $tableBlueprint
    ): ClassDefinition {

        $className = ClassNameTool::snakeCaseToClassName(
            null,
            $tableBlueprint->getName(),
            $this->getDataTransportClassSuffix()
        );

        $abstractClassName = $this->getAbstractClassPrefix().$className;

        $namespaceParts = explode('\\', $this->getDataTransportObjectNamespace());
        $namespaceParts[] = ClassNameTool::snakeCaseToClassName(
            null,
            $tableBlueprint->getSchemaBlueprint()->getSchemaName(),
            null
        );
        $namespace = implode('\\', $namespaceParts);

        $namespaceParts[] = 'Generated';
        $abstractNamespace = implode('\\', $namespaceParts);

        $dtoAbstractClassDefinition = new ClassDefinition($abstractClassName, $abstractNamespace);

        $dtoClassDefinition = new ClassDefinition($className, $namespace);
        $dtoClassDefinition->setParentClass($dtoAbstractClassDefinition->getFullyQualifiedName());

        foreach ($tableBlueprint->getColumnBlueprints() as $columnBlueprint) {
            $propertyName = Str::camel($columnBlueprint->getColumnName());
            $phpTypeEnum = $this->dataTypeMap->getPhpTypeEnumFromDatabaseType(
                $columnBlueprint->getDataType(),
                $columnBlueprint->getMaximumCharacters(),
                $columnBlueprint->getNumericPrecision(),
                $columnBlueprint->getNumericScale(),
                $columnBlueprint->getIsNullable()
            );

            $columnClassProperty = (new ClassPropertyDefinition($propertyName,$phpTypeEnum))
                ->withSetter()
                ->withGetter()
            ;

            $dtoAbstractClassDefinition->addProperty($columnClassProperty);
        }

        $classFormatter = new ClassFormatter();

        $dtoClassPhpCode = $classFormatter->format($dtoClassDefinition);
        $abstractDtoPhpCode = $classFormatter->format($dtoAbstractClassDefinition);
//        echo "\n---DTO Class---\n$dtoClassPhpCode\n\n\n---Base Class---\n$abstractDtoPhpCode\n\n";

        /*
         * Create the directory for the Data Transport Objects
         */
        $directory = \ltrim($namespace, '\\');
        $directory = \ltrim($directory, 'App\\');
        $directoryPathParts = explode('\\', $directory);
        $dtoClassFolder = app_path().DIRECTORY_SEPARATOR.\implode(DIRECTORY_SEPARATOR, $directoryPathParts);
        $abstractDtoClassFolder = $dtoClassFolder.DIRECTORY_SEPARATOR.'Generated';
        if (!is_dir($dtoClassFolder)) {
            \mkdir($dtoClassFolder, 0777, true);
        }
        if (!is_dir($abstractDtoClassFolder)) {
            \mkdir($abstractDtoClassFolder, 0777, true);
        }

        $dtoFilePath = $dtoClassFolder.DIRECTORY_SEPARATOR.$dtoClassDefinition->getName().'.php';
        $abstractDtoFilePath = $abstractDtoClassFolder.DIRECTORY_SEPARATOR.$dtoAbstractClassDefinition->getName().'.php';

        if (!\file_exists($dtoFilePath)) {
            \file_put_contents($dtoFilePath, $dtoClassPhpCode);
        }
        \file_put_contents($abstractDtoFilePath, $abstractDtoPhpCode);

        return $dtoClassDefinition;
    }

    private function getAbstractClassPrefix(): string
    {
        return 'Abstract';
    }

    private function getDataTransportObjectAbstractNamespace(): string
    {
        return "App\DataTransportObjects\Abstract";
    }

    private function getDataTransportObjectNamespace(): string
    {
        return "App\DataTransportObjects";
    }

    private function getDataTransportClassSuffix(): string
    {
        return 'Dto';
    }

    private function getDataTransportObjectPath(): string
    {
        return app_path('DataTransport');
    }
}
