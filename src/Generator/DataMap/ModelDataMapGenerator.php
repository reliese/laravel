<?php

namespace Reliese\Generator\DataMap;

use app\DataTransport\Objects\PrimaryDatabase\OrganizationDto;
use app\Services\Identity\AccountService;
use Illuminate\Support\Str;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\DataTransportObjectGeneratorConfiguration;
use Reliese\Configuration\ModelDataMapGeneratorConfiguration;
use Reliese\Generator\DataTransport\DataTransportGenerator;
use Reliese\Generator\Model\ModelGenerator;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Definition\FunctionParameterDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Definition\StatementBlockDefinition;
use Reliese\MetaCode\Definition\StatementDefinitionCollection;
use Reliese\MetaCode\Definition\TraitDefinition;
use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;
use Reliese\MetaCode\Writers\CodeWriter;
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
     * @var DataTransportObjectGeneratorConfiguration
     */
    private DataTransportObjectGeneratorConfiguration $dataTransportObjectGeneratorConfiguration;

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
     * @param ModelDataMapGeneratorConfiguration        $modelDataMapGeneratorConfiguration
     * @param DataTransportObjectGeneratorConfiguration $dataTransportObjectGeneratorConfiguration
     * @param ModelGenerator                            $modelGenerator
     * @param DataTransportGenerator                    $dataTransportGenerator
     */
    public function __construct(
        ModelDataMapGeneratorConfiguration $modelDataMapGeneratorConfiguration,
        DataTransportObjectGeneratorConfiguration $dataTransportObjectGeneratorConfiguration,
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
        $this->dataTransportObjectGeneratorConfiguration = $dataTransportObjectGeneratorConfiguration;
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
        /*
         * Look through FK Dto properties and Model FKs to assign DTO
         */
        /*
         * Example:
        if ($account->relationLoaded('organization')) {
            $organizationDto = $accountDto->getOrganizationDto() ?? new OrganizationDto();
            if ($this->organizationMap->toOrganizationDto($account->organization, $organizationDto)) {
                $accountDto->setOrganizationDto($organizationDto);
            } else {
                return false;
            }
        }
         */
        $requiredMapAccessorTraits = [];
        $fkDtoProperties = $this->dataTransportGenerator->getForeignKeyDtoPropertyDefinitions($tableBlueprint);
        foreach ($fkDtoProperties as $fkDtoProperty) {
//            $fkDtoProperty = $fkDtoProperties[$foreignKeyBlueprint->getName()];

            $fkDtoFullyQualifiedClassName = $fkDtoProperty->getPhpTypeEnum()->getFullyQualifiedObjectClassName();
            $fkDtoClassName = ClassNameTool::fullyQualifiedClassNameToClassName($fkDtoFullyQualifiedClassName);
            $fkModelName = \substr($fkDtoClassName, 0, \strlen($fkDtoClassName)-3);
            $modelRelationshipName = str::snake($fkModelName);
            $fkModelMapType = $fkModelName.'Map';
//            $fkModelMapTraitType = $this->modelDataMapGeneratorConfiguration->getAccessorTraitNamespace()
//                ."\\$fkModelMapType";
            $fkModelMapGetter = "get$fkModelMapType";
            $fkModelMapMapping = "to$fkDtoClassName";
            $relationshipName = str::snake($fkModelName);
            $fkDtoVariableName = ClassNameTool::dtoClassNameToVariableName($fkDtoClassName);
            $fkDtoGetterName = ClassNameTool::variableNameToGetterName($fkDtoVariableName);
            $fkDtoSetterName = ClassNameTool::variableNameToSetterName($fkDtoVariableName);

            if (!\array_key_exists($fkModelMapType, $requiredMapAccessorTraits)) {
                $requiredMapAccessorTraits[$fkModelMapType]
                    = $traitDefinition
                    = $this->generateModelDataMapAccessorTrait($this->getClassNamespaceByType($fkModelMapType));
                $modelMapAbstractClassDefinition->addTrait(new ClassTraitDefinition($traitDefinition->getFullyQualifiedName()));
            }

            $statements = new StatementBlockDefinition(
                /*
                 * example output:
                 * if ($account->relationLoaded('organization')) {
                 */
                new RawStatementDefinition(\sprintf("if (\$%s->relationLoaded('%s'))", $modelParameterName, $relationshipName))
            );
            $statements->addStatementDefinition(
                new RawStatementDefinition(
                    /*
                     * example output:
                     * $organizationDto = $accountDto->getOrganizationDto() ?? new OrganizationDto();
                     */
                    \sprintf(
                        "\$%s = \$%s->%s() ?? new %s();",
                        $fkDtoVariableName,
                        $dtoParameterName,
                        $fkDtoGetterName,
                        $fkDtoFullyQualifiedClassName
                    )
                )
            );

            $mapFkDtoStatement = new StatementBlockDefinition(
                new RawStatementDefinition(
                    /*
                     * example output:
                     * if ($this->getOrganizationMap()->toOrganizationDto($account->organization, $organizationDto))
                     */
                    \sprintf(
                        "if (\$this->%s()->%s(\$%s->%s, \$%s))",
                        $fkModelMapGetter,
                        $fkModelMapMapping,
                        $modelParameterName,
                        $modelRelationshipName,
                        $fkDtoVariableName
                    )
                )
            );
            $mapFkDtoStatement
                ->addStatementDefinition(
                    new RawStatementDefinition(
                        /*
                         * example output:
                         * $accountDto->setOrganizationDto($organizationDto);
                         */
                        \sprintf(
                            "\$%s->%s(\$%s);",
                            $dtoParameterName,
                            $fkDtoSetterName,
                            $fkDtoVariableName,
                        )
                    )
                )
                ->setBlockSuffixStatement(
                    (new StatementBlockDefinition(new RawStatementDefinition(" else ")))
                    ->addStatementDefinition(new RawStatementDefinition("return false;"))
                );

            $statements->addStatementDefinition($mapFkDtoStatement);
            $mapToDtoMethodDefinition->appendBodyStatement($statements);
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
            $propertyConstant = ClassPropertyDefinition::getPropertyNameConstantName(
                ClassNameTool::columnNameToPropertyName($columnBlueprint->getColumnName())
            );

            $dtoPropertyAssignmentStatement = new RawStatementDefinition(
                "\${$modelParameterName}[{$modelConstant}] = \${$dtoParameterName}->{$getterName}();"
            );

            if ($this->dataTransportObjectGeneratorConfiguration->getUseValueStateTracking()) {
                $conditionalAssignmentBlock
                    = new StatementBlockDefinition(
                        new RawStatementDefinition(\sprintf("if (\$%s->getValueState()->isInitialized(%s::%s))",
                            $dtoParameterName,
                            $dtoClassName,
                            $propertyConstant)));
                $conditionalAssignmentBlock->addStatementDefinition($dtoPropertyAssignmentStatement);
                $mapToDtoMethodDefinition->appendBodyStatement($conditionalAssignmentBlock);
            } else {
                $mapToDtoMethodDefinition->appendBodyStatement($dtoPropertyAssignmentStatement);
            }

            $mapToDtoMethodDefinition->appendBodyStatement($conditionalAssignmentBlock);
        }
        $mapToDtoMethodDefinition->appendBodyStatement(new RawStatementDefinition("return true;"));

        $modelMapAbstractClassDefinition->addMethodDefinition($mapToDtoMethodDefinition);

        /*
         * Write the Class Files
         */
        $codeWriter = new CodeWriter();
        foreach ($requiredMapAccessorTraits as $traitDefinition) {
            $traitSource = (new ClassFormatter())->format($traitDefinition);
            $filePath = $this->modelDataMapGeneratorConfiguration->getAccessorTraitPath().'/'
            .$traitDefinition->getClassName().".php";
            $codeWriter->overwriteClassDefinition($filePath, $traitSource);
        }
        $this->writeClassFiles($modelMapClassDefinition, $modelMapAbstractClassDefinition);
    }

    public function generateModelDataMapAccessorTrait(string $fullyQualifiedModelDataMapClass) : TraitDefinition
    {
        $dataMapClass = ClassNameTool::fullyQualifiedClassNameToClassName($fullyQualifiedModelDataMapClass);
        $dataMapNamespace = ClassNameTool::fullyQualifiedClassNameToNamespace($fullyQualifiedModelDataMapClass);

        $namespace = $this->modelDataMapGeneratorConfiguration->getAccessorTraitNamespace();
        $className = ClassNameTool::snakeCaseToClassName('With', $dataMapClass, null);

        $traitDefinition = new TraitDefinition($className, $namespace);

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

    private function getClassNamespaceByType(string $fkModelMapType): string
    {
        return $this->modelDataMapGeneratorConfiguration->getNamespace()."\\".$fkModelMapType;
    }
}
