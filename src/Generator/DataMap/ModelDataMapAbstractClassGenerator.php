<?php

namespace Reliese\Generator\DataMap;

use Illuminate\Support\Str;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\DataTransport\WithDataTransportObjectAbstractClassGenerator;
use Reliese\Generator\DataTransport\WithDataTransportObjectClassGenerator;
use Reliese\Generator\Model\WithModelClassGenerator;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Definition\FunctionParameterDefinition;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Definition\StatementBlockDefinition;
use Reliese\MetaCode\Definition\StatementDefinitionCollection;
use Reliese\MetaCode\Definition\TraitDefinition;
use Reliese\MetaCode\Enum\AbstractEnum;
use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;
use Reliese\MetaCode\Writers\CodeWriter;
use function sprintf;
/**
 * Class ModelDataMapAbstractClassGenerator
 */
class ModelDataMapAbstractClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;
    use WithModelClassGenerator;
    use WithDataTransportObjectClassGenerator;
    use WithModelDataMapClassGenerator;
    use WithDataTransportObjectAbstractClassGenerator;
    use WithModelDataMapClassAccessorGenerator;

    protected function allowClassFileOverwrite(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getGeneratedClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getGeneratedClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
            ->getGeneratedClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        /*
         * Determine the model class name and namespace
         */
        $modelObjectTypeDefinition = $this->getModelClassGenerator()->getObjectTypeDefinition($columnOwner);
        $modelParameterName = ClassNameTool::classNameToParameterName($modelObjectTypeDefinition->getImportableName());
        $modelParameterType = PhpTypeEnum::objectOfType($modelObjectTypeDefinition->getFullyQualifiedName());

        /*
         * Determine the dto Class name and namespace
         */
        $dtoObjectTypeDefinition = $this->getDataTransportObjectClassGenerator()->getObjectTypeDefinition($columnOwner);
        $dtoClassName = $dtoObjectTypeDefinition->getImportableName();
        $dtoParameterName = ClassNameTool::classNameToParameterName($dtoObjectTypeDefinition->getImportableName());
        $dtoParameterType = PhpTypeEnum::objectOfType($dtoObjectTypeDefinition->getFullyQualifiedName());

        /*
         * Determine the Map class name and namespace
         */
        $modelMapAbstractClassObjectTypeDefinition = $this->getObjectTypeDefinition($columnOwner);

        /*
         * Define the abstract map class
         */
        $modelMapAbstractClassDefinition = new ClassDefinition(
            $modelMapAbstractClassObjectTypeDefinition,
            AbstractEnum::abstractEnum()
        );

        $modelMapAbstractClassDefinition
            ->addImport($modelObjectTypeDefinition)
            ->addImport($dtoObjectTypeDefinition)
        ;

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

        foreach ($columnOwner->getColumnBlueprints() as $columnBlueprint) {
            $modelConstant = $modelObjectTypeDefinition->getImportableName().'::'.Str::upper($columnBlueprint->getColumnName());
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
        $fkDtoProperties = $this->getDataTransportObjectAbstractClassGenerator()
            ->getForeignKeyDtoPropertyDefinitions($columnOwner);

        foreach ($fkDtoProperties as $fkDtoProperty) {
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
            $fkDtoVariableName = ClassNameTool::classNameToVariableName($fkDtoClassName);
            $fkDtoGetterName = ClassNameTool::variableNameToGetterName($fkDtoVariableName);
            $fkDtoSetterName = ClassNameTool::variableNameToSetterName($fkDtoVariableName);

            if (!\array_key_exists($fkModelMapType, $requiredMapAccessorTraits)) {
                $requiredMapAccessorTraits[$fkModelMapType]
                    = $traitDefinition
                    = $this->getModelDataMapClassAccessorGenerator()
                        ->generateModelDataMapAccessorTrait($this->getClassNamespaceByType($fkModelMapType));
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

        foreach ($columnOwner->getColumnBlueprints() as $columnBlueprint) {
            $modelConstant = $modelObjectTypeDefinition->getImportableName().'::'.Str::upper($columnBlueprint->getColumnName());
            $getterName = ClassNameTool::columnNameToGetterName($columnBlueprint->getColumnName());
            $propertyConstant = ClassPropertyDefinition::getPropertyNameConstantName(
                ClassNameTool::columnNameToPropertyName($columnBlueprint->getColumnName())
            );

            $dtoPropertyAssignmentStatement = new RawStatementDefinition(
                "\${$modelParameterName}[{$modelConstant}] = \${$dtoParameterName}->{$getterName}();"
            );

            if ($this->getConfigurationProfile()->getDataTransportObjectGeneratorConfiguration()->getUseValueStateTracking()) {
                $conditionalAssignmentBlock
                    = new StatementBlockDefinition(
                    new RawStatementDefinition(\sprintf("if (\$%s->getValueWasInitialized(%s::%s))",
                        $dtoParameterName,
                        $dtoClassName,
                        $propertyConstant)));
                $conditionalAssignmentBlock->addStatementDefinition($dtoPropertyAssignmentStatement);
                $mapToDtoMethodDefinition->appendBodyStatement($conditionalAssignmentBlock);
            } else {
                $mapToDtoMethodDefinition->appendBodyStatement($dtoPropertyAssignmentStatement);
            }
        }
        $mapToDtoMethodDefinition->appendBodyStatement(new RawStatementDefinition("return true;"));

        $modelMapAbstractClassDefinition->addMethodDefinition($mapToDtoMethodDefinition);

        return $modelMapAbstractClassDefinition;
    }



    private function getClassNamespaceByType(string $fkModelMapType): string
    {
        return $this->getConfigurationProfile()->getModelDataMapGeneratorConfiguration()
                ->getClassNamespace()."\\".$fkModelMapType;
    }
}