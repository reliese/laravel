<?php

namespace Reliese\Generator\Validator;

use Illuminate\Support\Str;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Database\WithPhpTypeMap;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\DataMap\WithModelDataMapClassAccessorGenerator;
use Reliese\Generator\DataMap\WithModelDataMapClassGenerator;
use Reliese\Generator\DataTransport\WithDataTransportObjectAbstractClassGenerator;
use Reliese\Generator\DataTransport\WithDataTransportObjectClassGenerator;
use Reliese\Generator\Model\WithModelAbstractClassGenerator;
use Reliese\Generator\Model\WithModelClassGenerator;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassConstantDefinition;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\CommentBlockStatementDefinition;
use Reliese\MetaCode\Definition\FunctionParameterDefinition;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Definition\StatementBlockDefinition;
use Reliese\MetaCode\Definition\StatementDefinitionCollection;
use Reliese\MetaCode\Definition\StatementDefinitionInterface;
use Reliese\MetaCode\Enum\AbstractEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Tool\ClassNameTool;
use function array_key_exists;
use function sprintf;
/**
 * Class DtoValidatorAbstractClassGenerator
 */
class DtoValidatorAbstractClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;
    use WithPhpTypeMap;
    use WithModelAbstractClassGenerator;
    use WithModelClassGenerator;
    use WithModelDataMapClassGenerator;
    use WithModelDataMapClassAccessorGenerator;
    use WithDataTransportObjectAbstractClassGenerator;
    use WithDataTransportObjectClassGenerator;

    /** @var ClassPropertyDefinition[] */
    private array $generatedForeignKeyDtoPropertyDefinitions = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $generatedRequireDtoPropertyMethods = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $generatedRequireValueMethods = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $generatedValidateDtoPropertyMethods = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $generatedValidateValueMethods = [];

    /**
     * @return bool
     */
    protected function allowClassFileOverwrite(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()->getValidatorGeneratorConfiguration()
            ->getGeneratedClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getValidatorGeneratorConfiguration()
            ->getGeneratedClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getValidatorGeneratorConfiguration()
            ->getGeneratedClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $abstractClassDefinition = new ClassDefinition(
            $this->getObjectTypeDefinition($columnOwner),
            AbstractEnum::abstractEnum()
        );

        $dtoAbstractClassDefinition = $this->getDataTransportObjectAbstractClassGenerator()
            ->getClassDefinition($columnOwner);

        $dtoClassDefinition = $this->getDataTransportObjectClassGenerator()
            ->getClassDefinition($columnOwner);

        $abstractClassDefinition->addImport($dtoClassDefinition);

        $this->addRequireAndValidateMethods($dtoAbstractClassDefinition,
            $abstractClassDefinition,
            $dtoClassDefinition
        );

        $this->addFkValidations(
            $dtoAbstractClassDefinition,
            $abstractClassDefinition,
            $dtoClassDefinition
        );

        /*
         * Add require method
         */

        //            $requireFieldMethodDefinition = new ClassMethodDefinition(
        //                $requireFieldMethodName,
        //                PhpTypeEnum::staticTypeEnum(),
        //                [$invalidMessageParameter]
        //            );
        //
        //            $requireFieldMethodDefinition->appendBodyStatement(
        //                new RawStatementDefinition("return \$this;")
        //            );
        //
        //            $abstractClassDefinition->addMethodDefinition($requireFieldMethodDefinition);

        return $abstractClassDefinition;
    }

    /**
     * @param ClassDefinition $dtoAbstractClassDefinition
     * @param ClassDefinition $dtoValidatorAbstractClassDefinition
     * @param ClassDefinition $dtoClassDefinition
     */
    private function addRequireAndValidateMethods(
        ClassDefinition $dtoAbstractClassDefinition,
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition
    ): void {
        foreach ($dtoAbstractClassDefinition->getProperties() as $dtoPropertyDefinition) {
            if (!$dtoPropertyDefinition->hasColumnBlueprint()) {
                // unable to determine validation method w/o column blueprint
                continue;
            }

            // Apply configuration filters to columns
            if ($this->isExcludedColumn($dtoPropertyDefinition->getColumnBlueprint())) {
                continue;
            }

            $dtoValidatorAbstractClassDefinition->addMethodDefinition(
                $this->generateRequireDtoPropertyMethod(
                    $dtoValidatorAbstractClassDefinition,
                    $dtoClassDefinition,
                    $dtoPropertyDefinition
                )
            );

            $dtoValidatorAbstractClassDefinition->addMethodDefinition(
                $this->generateValidateDtoPropertyMethod(
                    $dtoValidatorAbstractClassDefinition,
                    $dtoClassDefinition,
                    $dtoPropertyDefinition
                )
            );

            $dtoValidatorAbstractClassDefinition->addMethodDefinition(
                $this->generateRequireValueMethod(
                    $dtoValidatorAbstractClassDefinition,
                    $dtoClassDefinition,
                    $dtoPropertyDefinition
                )
            );

            $dtoValidatorAbstractClassDefinition->addMethodDefinition(
                $this->generateValidateValueMethod(
                    $dtoValidatorAbstractClassDefinition,
                    $dtoClassDefinition,
                    $dtoPropertyDefinition
                )
            );
        }
    }

    private function addFkValidations(ClassDefinition $dtoAbstractClassDefinition,
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition)
    {
        foreach ($dtoAbstractClassDefinition->getProperties() as $dtoPropertyDefinition) {

            if (!$dtoPropertyDefinition->hasForeignKeyBlueprint()) {
                // unable to determine validation method w/o Foreign Key Blueprint
                continue;
            }

            $requireDtoValidationMessageIndexConstant = $this->generateRequireDtoValidationMessageConstant(
                $dtoClassDefinition,
                $dtoPropertyDefinition
            );

            $dtoParameter = $this->getNullableDtoFunctionParameterDefinition($dtoClassDefinition);

            $returnIsInvalidStatement
                = $this->generateReturnInvalidWithSimpleMessageConstant($requireDtoValidationMessageIndexConstant);

            $dtoPropertyTypeDefinition = $dtoPropertyDefinition->getPhpTypeEnum()->toObjectTypeDefinition();
            $dtoAbstractClassDefinition->addImport(
                $dtoPropertyTypeDefinition
            );

            $ifIsValidBlock = new StatementBlockDefinition(
                new RawStatementDefinition(
                    sprintf(
                        "if (\$%s->%s() instanceOf %s)",
                        $dtoParameter->getParameterName(),
                        $dtoPropertyDefinition->getGetterMethodName(),
                        $dtoPropertyTypeDefinition->getFullyQualifiedName()
                    )
                )
            );
            $ifIsValidBlock->addStatementDefinition($this->getReturnIsValidStatement());

            $methodDefinition = new ClassMethodDefinition(
                $this->getRequireDtoMethodName($dtoPropertyDefinition),
                $this->getValidateMethodResponsePhpTypeEnum(),
                [$dtoParameter]
            );
            $methodDefinition
                ->appendBodyStatement($ifIsValidBlock)
                ->appendBodyStatement($returnIsInvalidStatement);

            $dtoValidatorAbstractClassDefinition
                ->addConstant($requireDtoValidationMessageIndexConstant)
                ->addMethodDefinition($methodDefinition)
            ;
        }
    }

    private function isExcludedColumn(ColumnBlueprint $columnBlueprint): bool
    {
        return $this->getConfigurationProfile()->getValidatorGeneratorConfiguration()->getDatabaseFilters()
            ->isExcludedColumn(
                $columnBlueprint->getColumnOwner()->getSchemaBlueprint()->getSchemaName(),
                $columnBlueprint->getColumnOwner()->getName(),
                $columnBlueprint->getColumnName()
            );
    }

    private function generateRequireDtoPropertyMethod(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition
    ) : ClassMethodDefinition {
        $memoKey = $dtoValidatorAbstractClassDefinition->getClassName().'::'.$dtoPropertyDefinition->getVariableName();
        if (array_key_exists($memoKey, $this->generatedRequireDtoPropertyMethods)) {
            return $this->generatedRequireDtoPropertyMethods[$memoKey];
        }
        $functionName = $this->getRequireDtoPropertyMethodName($dtoPropertyDefinition);
        $dtoParameter = $this->getNullableDtoFunctionParameterDefinition($dtoClassDefinition);

        $returnPhpEnumType = $this->getRequireMethodResponsePhpTypeEnum();

        $dtoValidatorAbstractClassDefinition
            ->addImport($this->getValidateMethodResponseObjectTypeDefinition())
            ->addImport($this->getTranslatableMessageObjectTypeDefinition());

        $ifDtoIsNullStatement = $this->generateIfDtoPropertyNullStatement(
            $dtoValidatorAbstractClassDefinition,
            $dtoClassDefinition,
            $dtoPropertyDefinition,
            $dtoParameter,
        );

        $returnValidationResultStatement = new RawStatementDefinition(
            sprintf(
                "return \$this->%s(\$%s->%s());",
                $this->getRequireValueMethodName($dtoPropertyDefinition),
                $dtoParameter->getParameterName(),
                $dtoPropertyDefinition->getGetterMethodName()
            )
        );

        $requireDtoFunction = new ClassMethodDefinition(
            $functionName,
            $returnPhpEnumType,
            [$dtoParameter]
        );

        $requireDtoFunction
            ->appendBodyStatement($ifDtoIsNullStatement)
            ->appendBodyStatement($returnValidationResultStatement);

        return $this->generatedRequireDtoPropertyMethods[$memoKey] = $requireDtoFunction;
    }


    private function generateValidateDtoPropertyMethod(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition
    ) : ClassMethodDefinition {
        $memoKey = $dtoValidatorAbstractClassDefinition->getClassName()."::".$dtoPropertyDefinition->getVariableName();
        if (array_key_exists($memoKey, $this->generatedValidateDtoPropertyMethods)) {
            return $this->generatedValidateDtoPropertyMethods[$memoKey];
        }

        $functionName = $this->getValidateDtoPropertyMethodName($dtoPropertyDefinition);
        $dtoParameter = $this->getNullableDtoFunctionParameterDefinition($dtoClassDefinition);
        $returnPhpEnumType = $this->getValidateMethodResponsePhpTypeEnum();

        $dtoValidatorAbstractClassDefinition
            ->addImport($this->getValidateMethodResponseObjectTypeDefinition())
            ->addImport($this->getTranslatableMessageObjectTypeDefinition());

        $ifDtoIsNullStatement = $this->generateIfDtoPropertyNullStatement(
            $dtoValidatorAbstractClassDefinition,
            $dtoClassDefinition,
            $dtoPropertyDefinition,
            $dtoParameter,
        );

        $returnValidationResultStatement = $this->generateCallValidateValueMethod(
            $dtoPropertyDefinition,
            sprintf(
                "\$%s->%s()",
                $dtoParameter->getParameterName(),
                $dtoPropertyDefinition->getGetterMethodName()
            )
        );

        $validateDtoFunction = new ClassMethodDefinition(
            $functionName,
            $returnPhpEnumType,
            [$dtoParameter]
        );

        $validateDtoFunction
            ->appendBodyStatement($ifDtoIsNullStatement)
            ->appendBodyStatement($returnValidationResultStatement);

        return $this->generatedValidateDtoPropertyMethods[$memoKey] = $validateDtoFunction;
    }


    private function generateRequireValueMethod(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition
    ): ClassMethodDefinition {
        $memoKey = $dtoValidatorAbstractClassDefinition->getClassName().'::'.$dtoPropertyDefinition->getVariableName();
        if (array_key_exists($memoKey, $this->generatedRequireValueMethods)) {
            return $this->generatedRequireValueMethods[$memoKey];
        }
        $valueParameter = new FunctionParameterDefinition($dtoPropertyDefinition->getVariableName(), PhpTypeEnum::mixedType());

        $ifEmptyStatement = $this->generateRequireIfEmptyStatement(
            $dtoValidatorAbstractClassDefinition,
            $dtoClassDefinition,
            $dtoPropertyDefinition,
            "\$".$valueParameter->getParameterName(),
        );

        $returnValidationResultStatement = $this->generateCallValidateValueMethod(
            $dtoPropertyDefinition,
            "\$".$valueParameter->getParameterName(),
        );

        $dtoValidatorAbstractClassDefinition->addImport($this->getValidateMethodResponseObjectTypeDefinition());

        $classMethodDefinition = new ClassMethodDefinition(
            $this->getRequireValueMethodName($dtoPropertyDefinition),
            $this->getValidateMethodResponsePhpTypeEnum(),
            [$valueParameter]
        );

        $classMethodDefinition
            ->appendBodyStatement($ifEmptyStatement)
            ->appendBodyStatement($returnValidationResultStatement)
        ;

        return $this->generatedRequireValueMethods[$memoKey] = $classMethodDefinition;
    }

    private function generateValidateValueMethod(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition
    ): ClassMethodDefinition
    {
        $memoKey = $dtoValidatorAbstractClassDefinition->getClassName().'::'.$dtoPropertyDefinition->getVariableName();
        if (array_key_exists($memoKey, $this->generatedValidateValueMethods)) {
            return $this->generatedValidateValueMethods[$memoKey];
        }

        $valueParameter = new FunctionParameterDefinition($dtoPropertyDefinition->getVariableName(), PhpTypeEnum::mixedType());

        $dtoValidatorAbstractClassDefinition
            ->addImport($this->getValidateMethodResponseObjectTypeDefinition())
            ->addImport($this->getTranslatableMessageObjectTypeDefinition());

        /*
         * If column does not allow null, then ensure value is not null
         */
        $ifNullStatementBlock = $this->generateValidateNullabilityStatements(
            $dtoValidatorAbstractClassDefinition,
            $dtoClassDefinition,
            $dtoPropertyDefinition,
            $valueParameter,
            $this->getRequireValueMethodName($dtoPropertyDefinition)
        );

        /*
         * If value is of the wrong type, then it is invalid
         */
        $ifIncorrectTypeBlock = $this->generateIfPropertyValueIsNotType(
            $dtoValidatorAbstractClassDefinition,
            $dtoClassDefinition,
            $dtoPropertyDefinition,
            $valueParameter
        );

        /*
         * If the column defines value ranges, such as max value or max length, then enforce those
         */
        $valueLimitStatements = $this->generatePropertyValueLimitChecks(
            $dtoValidatorAbstractClassDefinition,
            $dtoClassDefinition,
            $dtoPropertyDefinition,
            $valueParameter
        );

        $classMethodDefinition = new ClassMethodDefinition(
            $this->getValidateValueMethodName($dtoPropertyDefinition),
            $this->getValidateMethodResponsePhpTypeEnum(),
            [$valueParameter]
        );

        $classMethodDefinition
            ->appendBodyStatement($ifNullStatementBlock)
            ->appendBodyStatement($ifIncorrectTypeBlock)
        ;
        if (!empty($valueLimitStatements)) {
            $classMethodDefinition->appendBodyStatement($valueLimitStatements);
        }
        $classMethodDefinition->appendBodyStatement($this->getReturnIsValidStatement());

        return $this->generatedValidateValueMethods[$memoKey] = $classMethodDefinition;
    }

    private function generateRequireDtoValidationMessageConstant(
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
    ) {
        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName())));
        $propertyNameUpperSnakeCase = Str::upper(Str::snake(Str::singular(
            $dtoPropertyDefinition->getForeignKeyBlueprint()->getReferencedTableBlueprint()->getName()
        )));

        $constantName = sprintf("%s_%s_DATA_REQUIRED",
            $tableName,
            $propertyNameUpperSnakeCase
        );

        $constantValue = sprintf(
            "VALIDATION.%s.%s.DATA_REQUIRED",
            $tableName,
            $propertyNameUpperSnakeCase
        );

        /*
         * Create type message constant
         */
        return new ClassConstantDefinition(
            $constantName,
            $constantValue,
            VisibilityEnum::publicEnum(),
            PhpTypeEnum::stringType()
        );
    }
    private function getNullableDtoFunctionParameterDefinition(ClassDefinition $dtoClassDefinition): FunctionParameterDefinition
    {
        return new FunctionParameterDefinition(
            ClassNameTool::classNameToVariableName($dtoClassDefinition->getClassName()),
            PhpTypeEnum::nullableObjectOfType($dtoClassDefinition->getFullyQualifiedName())
        );
    }

    /**
     * @param ClassConstantDefinition $validationMessageIndexConstant
     *
     * @return StatementDefinitionInterface
     */
    private function generateReturnInvalidWithSimpleMessageConstant(
        ClassConstantDefinition $validationMessageIndexConstant,
    ): StatementDefinitionInterface {
        return new RawStatementDefinition(
            sprintf("return %s::invalid([new %s(%s)]);",
                $this->getRequireMethodResponseObjectTypeDefinition()->getImportableName(),
                $this->getTranslatableMessageObjectTypeDefinition()->getImportableName(),
                $validationMessageIndexConstant->toSelfReference()
            )
        );
    }

    /**
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return string
     */
    private function getRequireDtoMethodName(
        ClassPropertyDefinition $dtoPropertyDefinition
    ): string {
        return sprintf(
            "requireDto%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

    /**
     * @return PhpTypeEnum
     */
    private function getValidateMethodResponsePhpTypeEnum(): PhpTypeEnum
    {
        return PhpTypeEnum::objectOfType($this->getValidateMethodResponseObjectTypeDefinition()->getFullyQualifiedName());
    }

    /**
     * @return StatementDefinitionInterface
     */
    private function getReturnIsValidStatement(): StatementDefinitionInterface
    {
        return new RawStatementDefinition(
            sprintf(
                "return %s::valid();",
                $this->getValidateMethodResponseObjectTypeDefinition()->getImportableName())
        );
    }

    /**
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return string
     */
    private function getRequireDtoPropertyMethodName(
        ClassPropertyDefinition $dtoPropertyDefinition
    ): string {
        return sprintf(
            "requireDto%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

    /**
     * @return PhpTypeEnum
     */
    private function getRequireMethodResponsePhpTypeEnum(): PhpTypeEnum
    {
        return PhpTypeEnum::objectOfType($this->getRequireMethodResponseObjectTypeDefinition()
            ->getFullyQualifiedName());
    }

    /**
     * @return ObjectTypeDefinition
     */
    private function getTranslatableMessageObjectTypeDefinition() : ObjectTypeDefinition
    {
        return new ObjectTypeDefinition('\Symfony\Component\Translation\TranslatableMessage');
    }

    /**
     * @param ClassDefinition             $dtoValidatorAbstractClassDefinition
     * @param ClassDefinition             $dtoClassDefinition
     * @param ClassPropertyDefinition     $dtoPropertyDefinition
     * @param FunctionParameterDefinition $dtoParameter
     *
     * @return StatementDefinitionInterface
     */
    private function generateIfDtoPropertyNullStatement(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
        FunctionParameterDefinition $dtoParameter
    ): StatementDefinitionInterface {

        $propertyRequiredConstantDefinition = $this->generatePropertyRequiredClassConstant(
            $dtoClassDefinition,
            $dtoPropertyDefinition
        );

        $dtoValidatorAbstractClassDefinition->addConstant($propertyRequiredConstantDefinition);

        if ($dtoParameter->getParameterType()->hasFullyQualifiedObjectClassName()) {
            $dtoValidatorAbstractClassDefinition->addImport($dtoParameter->getParameterType()
                ->toObjectTypeDefinition());
        }

        $isNotAnInstanceOfDtoType = $dtoParameter->getParameterType()->toVariableTypeTest("\$"
            .$dtoParameter->getParameterName(), true);

        $ifParameterIsNullStatement = new RawStatementDefinition("if ($isNotAnInstanceOfDtoType)");

        $returnIsInvalidStatement
            = $this->generateReturnInvalidWithSimpleMessageConstant($propertyRequiredConstantDefinition);

        return (new StatementBlockDefinition($ifParameterIsNullStatement))
            ->addStatementDefinition($returnIsInvalidStatement);
    }

    /**
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return string
     */
    private function getRequireValueMethodName(
        ClassPropertyDefinition $dtoPropertyDefinition
    ): string {
        return sprintf(
            "require%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

    /**
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return string
     */
    private function getValidateDtoPropertyMethodName(ClassPropertyDefinition $dtoPropertyDefinition)
    {
        return sprintf(
            "validateDto%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

    /**
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     * @param string                  $valueStatement
     *
     * @return StatementDefinitionInterface
     */
    private function generateCallValidateValueMethod(
        ClassPropertyDefinition $dtoPropertyDefinition,
        string $valueStatement,
    ): StatementDefinitionInterface
    {
        return new RawStatementDefinition(
            sprintf(
                "return \$this->%s(%s);",
                $this->getValidateValueMethodName($dtoPropertyDefinition),
                $valueStatement
            )
        );
    }

    /**
     * @param ClassDefinition         $dtoValidatorAbstractClassDefinition
     * @param ClassDefinition         $dtoClassDefinition
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     * @param string                  $valueStatement
     *
     * @return StatementDefinitionInterface
     */
    private function generateRequireIfEmptyStatement(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
        string $valueStatement
    ): StatementDefinitionInterface {

        $additionalCheck = "";
        if ($dtoPropertyDefinition->getPhpTypeEnum()->isAnyBool()) {
            $additionalCheck = " && false !== $valueStatement";
        }

        $ifEmptyStatement = new RawStatementDefinition("if (empty($valueStatement)$additionalCheck)");

        return (new StatementBlockDefinition($ifEmptyStatement))
            ->addStatementDefinition(
                $this->generateReturnInvalidWithPropertyRequiredMessage(
                    $dtoValidatorAbstractClassDefinition,
                    $dtoClassDefinition,
                    $dtoPropertyDefinition
                )
            );
    }

    /**
     * @param ClassDefinition             $dtoValidatorAbstractClassDefinition
     * @param ClassDefinition             $dtoClassDefinition
     * @param ClassPropertyDefinition     $dtoPropertyDefinition
     * @param FunctionParameterDefinition $propertyValueParameter
     * @param string                      $requireValueMethodName
     *
     * @return StatementDefinitionInterface
     */
    private function generateValidateNullabilityStatements(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
        FunctionParameterDefinition $propertyValueParameter,
        string $requireValueMethodName
    ) : StatementDefinitionInterface {

        $commentBlock = new CommentBlockStatementDefinition();
        $statements = new StatementDefinitionCollection();
        $allowNull = false;

        if ($dtoPropertyDefinition->getColumnBlueprint()->getIsNullable()) {
            $commentBlock->addLine("Null is considered valid because column allows nulls.");
            $allowNull = true;
        } elseif ($dtoPropertyDefinition->getColumnBlueprint()->getIsAutoincrement()) {
            $commentBlock->addLine("Null is considered valid because the database generates this value.");
            $allowNull = true;
        }

        if ($allowNull) {
            $commentBlock->addLine(
                "Call $requireValueMethodName instead of this method inorder to require a value be present"
            );
            return $statements
                ->addStatementDefinition($commentBlock)
                ->addStatementDefinition(
                    (new StatementBlockDefinition(new RawStatementDefinition(
                        sprintf("if (is_null(\$%s))", $propertyValueParameter->getParameterName())
                    )))
                        ->addStatementDefinition($this->getReturnIsValidStatement())
                );
        }

        /**
         * Null is not allowed...
         */
        $commentBlock->addLine("Null is considered invalid because the database column does not allow null.");

        return $statements
            ->addStatementDefinition($commentBlock)
            ->addStatementDefinition(
                (new StatementBlockDefinition(
                    new RawStatementDefinition(
                        sprintf("if (is_null(\$%s))", $propertyValueParameter->getParameterName())
                    )
                ))
                    ->addStatementDefinition(
                        $this->generateReturnInvalidWithPropertyRequiredMessage(
                            $dtoValidatorAbstractClassDefinition,
                            $dtoClassDefinition,
                            $dtoPropertyDefinition,
                        )
                    )
            );
    }

    /**
     * @param ClassDefinition             $dtoValidatorAbstractClassDefinition
     * @param ClassDefinition             $dtoClassDefinition
     * @param ClassPropertyDefinition     $dtoPropertyDefinition
     * @param FunctionParameterDefinition $propertyValueParameter
     *
     * @return StatementDefinitionInterface
     */
    private function generateIfPropertyValueIsNotType(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
        FunctionParameterDefinition $propertyValueParameter
    ): StatementDefinitionInterface {

        if (!$dtoPropertyDefinition->getPhpTypeEnum()->hasTypeTest()) {
            return (new CommentBlockStatementDefinition())->addLine(
                sprintf("Unable to perform a type test for fields of type '%s'", $dtoPropertyDefinition->getPhpTypeEnum()->toDeclarationType())
            );
        }

        $propertyTypeConstantDefinition = $this->generatePropertyTypeClassConstant(
            $dtoClassDefinition,
            $dtoPropertyDefinition
        );
        $dtoValidatorAbstractClassDefinition->addConstant($propertyTypeConstantDefinition);

        if ($dtoPropertyDefinition->getPhpTypeEnum()->hasFullyQualifiedObjectClassName()) {
            $dtoValidatorAbstractClassDefinition->addImport(
                $dtoPropertyDefinition->getPhpTypeEnum()->toObjectTypeDefinition());
        }

        $isIncorrectTypeStatement = new RawStatementDefinition(
            sprintf("if (%s)",
                $dtoPropertyDefinition->getPhpTypeEnum()->toVariableTypeTest(
                    "\$".$propertyValueParameter->getParameterName(),
                    true
                )
            )
        );

        $returnIncorrectType = new RawStatementDefinition(
            sprintf(
                "return %s::invalid([new %s(%s)]);",
                $this->getRequireMethodResponseObjectTypeDefinition()->getImportableName(),
                $this->getTranslatableMessageObjectTypeDefinition()->getImportableName(),
                $propertyTypeConstantDefinition->toSelfReference(),
            )
        );

        return (new StatementBlockDefinition($isIncorrectTypeStatement))
            ->addStatementDefinition($returnIncorrectType);
    }

    /**
     * @param ClassDefinition             $dtoValidatorAbstractClassDefinition
     * @param ClassDefinition             $dtoClassDefinition
     * @param ClassPropertyDefinition     $dtoPropertyDefinition
     * @param FunctionParameterDefinition $propertyValueParameter
     *
     * @return StatementDefinitionInterface
     */
    private function generatePropertyValueLimitChecks(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
        FunctionParameterDefinition $propertyValueParameter,
    ) : StatementDefinitionInterface {

        $statements = new StatementDefinitionCollection();

        $getPropertyValueSyntax = sprintf("\$%s", $propertyValueParameter->getParameterName());

        if ($dtoPropertyDefinition->getColumnBlueprint()->hasCharacterLimit()) {

            $statements->addStatementDefinition(
                $this->generateStringMaxLengthValidationBlock(
                    $dtoValidatorAbstractClassDefinition,
                    $dtoClassDefinition,
                    $dtoPropertyDefinition,
                    $getPropertyValueSyntax
                )
            );
        }


        if ($dtoPropertyDefinition->getColumnBlueprint()->getIsUnsigned()) {
            $statements->addStatementDefinition(
                $this->generateIsPositiveValueValidationBlock(
                    $dtoValidatorAbstractClassDefinition,
                    $dtoClassDefinition,
                    $dtoPropertyDefinition,
                    $getPropertyValueSyntax
                )
            );
        }

        return $statements;
    }

    /**
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return string
     */
    private function getValidateValueMethodName(ClassPropertyDefinition $dtoPropertyDefinition)
    {
        return sprintf(
            "validate%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

    /**
     * @return ObjectTypeDefinition
     */
    private function getRequireMethodResponseObjectTypeDefinition(): ObjectTypeDefinition
    {
        return new ObjectTypeDefinition('\app\Patterns\MethodResponses\ValidateMethodResponse');
    }

    /**
     * @return ObjectTypeDefinition
     */
    private function getValidateMethodResponseObjectTypeDefinition(): ObjectTypeDefinition
    {
        return new ObjectTypeDefinition('\app\Patterns\MethodResponses\ValidateMethodResponse');
    }

    /**
     * @param ClassDefinition         $dtoClassDefinition
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return ClassConstantDefinition
     */
    private function generatePropertyRequiredClassConstant(
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
    ): ClassConstantDefinition {
        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName())));
        $dtoClassNameAsUpperSnakeCase = Str::upper(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName()));
        /*
         * Create required message constant
         */
        return new ClassConstantDefinition(sprintf("%s_%s_REQUIRED",
            $tableName,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()))), sprintf("VALIDATION.%s.%s.REQUIRED",
            $dtoClassNameAsUpperSnakeCase,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()))));
    }

    /**
     * @param ClassConstantDefinition $propertyRequiredConstantDefinition
     *
     * @return RawStatementDefinition
     */
    private function generateReturnInvalidWithPropertyRequiredMessage(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
    ): RawStatementDefinition {

        $propertyRequiredConstantDefinition = $this->generatePropertyRequiredClassConstant(
            $dtoClassDefinition,
            $dtoPropertyDefinition
        );

        $dtoValidatorAbstractClassDefinition->addConstant($propertyRequiredConstantDefinition);

        return new RawStatementDefinition(
            sprintf(
                "return %s::invalid([new %s(%s)]);",
                $this->getRequireMethodResponseObjectTypeDefinition()->getImportableName(),
                $this->getTranslatableMessageObjectTypeDefinition()->getImportableName(),
                $propertyRequiredConstantDefinition->toSelfReference(),
            )
        );
    }

    /**
     * @param ClassDefinition         $dtoClassDefinition
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return ClassConstantDefinition
     */
    private function generatePropertyTypeClassConstant(
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
    ): ClassConstantDefinition {

        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName())));

        $dtoClassNameAsUpperSnakeCase = Str::upper(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName()));

        $constantName = sprintf("%s_%s_MUST_BE_%s",
            $tableName,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName())),
            $dtoPropertyDefinition->getPhpTypeEnum()->toUpperSnakeCase()
        );

        $constantValue = sprintf(
            "VALIDATION.%s.%s.MUST_BE_%s",
            $dtoClassNameAsUpperSnakeCase,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName())),
            $dtoPropertyDefinition->getPhpTypeEnum()->toUpperSnakeCase()
        );

        /*
         * Create type message constant
         */
        return new ClassConstantDefinition($constantName, $constantValue, VisibilityEnum::publicEnum());
    }

    /**
     * @param ClassDefinition         $dtoValidatorAbstractClassDefinition
     * @param ClassDefinition         $dtoClassDefinition
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     * @param string                  $variableValueCall
     *
     * @return StatementBlockDefinition
     */
    private function generateStringMaxLengthValidationBlock(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
        string $variableValueCall
    ) {
        $maxLengthConstant = $this->generatePropertyMaxLengthConstant(
            $dtoClassDefinition,
            $dtoPropertyDefinition
        );

        $propertyMaxLengthValidationMessageConstant = $this->generatePropertyMaxLengthValidationMessageConstant(
            $dtoClassDefinition,
            $dtoPropertyDefinition
        );

        $dtoValidatorAbstractClassDefinition
            ->addConstant($propertyMaxLengthValidationMessageConstant)
            ->addConstant($maxLengthConstant)
        ;

        $ifStatement = new RawStatementDefinition(
            sprintf(
                "if (%s < \mb_strlen(%s))",
                $maxLengthConstant->toSelfReference(),
                $variableValueCall
            )
        );

        $returnStatement = new RawStatementDefinition(
            sprintf(
                "return %s::invalid([new %s(%s, ['MAX' => %s])]);",
                $this->getRequireMethodResponseObjectTypeDefinition()->getImportableName(),
                $this->getTranslatableMessageObjectTypeDefinition()->getImportableName(),
                $propertyMaxLengthValidationMessageConstant->toSelfReference(),
                $maxLengthConstant->toSelfReference()
            )
        );

        return (new StatementBlockDefinition($ifStatement))->addStatementDefinition($returnStatement);
    }

    /**
     * @param ClassDefinition         $dtoValidatorAbstractClassDefinition
     * @param ClassDefinition         $dtoClassDefinition
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     * @param string                  $valueToTest
     *
     * @return StatementDefinitionInterface
     */
    private function generateIsPositiveValueValidationBlock(ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
        string $valueToTest
    ): StatementDefinitionInterface {
        $propertyPositiveValueValidationMessageConstant = $this->generatePropertyPositiveValueValidationMessageConstant(
            $dtoClassDefinition,
            $dtoPropertyDefinition
        );

        $dtoValidatorAbstractClassDefinition->addConstant($propertyPositiveValueValidationMessageConstant);

        $ifStatement = new RawStatementDefinition(sprintf("if (0 > %s)", $valueToTest));

        $returnStatement = new RawStatementDefinition(
            sprintf(
                "return %s::invalid([new %s(%s)]);",
                $this->getRequireMethodResponseObjectTypeDefinition()->getImportableName(),
                $this->getTranslatableMessageObjectTypeDefinition()->getImportableName(),
                $propertyPositiveValueValidationMessageConstant->toSelfReference(),
            )
        );

        return (new StatementBlockDefinition($ifStatement))->addStatementDefinition($returnStatement);
    }

    /**
     * @param ClassDefinition         $dtoClassDefinition
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return ClassConstantDefinition
     */
    private function generatePropertyMaxLengthConstant(
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
    ): ClassConstantDefinition {

        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName())));

        $constantName = sprintf("%s_%s_MAX_LENGTH",
            $tableName,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()))
        );

        /*
         * Create type message constant
         */
        return new ClassConstantDefinition(
            $constantName,
            $dtoPropertyDefinition->getColumnBlueprint()->getMaximumCharacters(),
            VisibilityEnum::publicEnum(),
            PhpTypeEnum::intType()
        );
    }

    /**
     * @param ClassDefinition         $dtoClassDefinition
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return ClassConstantDefinition
     */
    private function generatePropertyMaxLengthValidationMessageConstant(
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
    ): ClassConstantDefinition {
        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName())));
        $dtoClassNameAsUpperSnakeCase = Str::upper(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName()));

        $constantName =sprintf(
            "%s_%s_MAX_LENGTH",
            $tableName,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()))
        );

        $constantValue = sprintf(
            "VALIDATION.%s.%s.MAX_LENGTH",
            $dtoClassNameAsUpperSnakeCase,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()))
        );

        /*
         * Create required message constant
         */
        return new ClassConstantDefinition($constantName, $constantValue, VisibilityEnum::publicEnum());
    }

    /**
     * @param ClassDefinition         $dtoClassDefinition
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return ClassConstantDefinition
     */
    private function generatePropertyPositiveValueValidationMessageConstant(
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
    ): ClassConstantDefinition {
        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName())));
        $dtoClassNameAsUpperSnakeCase = Str::upper(Str::singular($dtoClassDefinition->getOriginatingBlueprint()->getName()));
        /*
         * Create required message constant
         */
        $constantName = sprintf("%s_%s_MUST_BE_POSITIVE_VALUE",
            $tableName,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()))
        );

        $constantValue = sprintf("VALIDATION.%s.%s.MUST_BE_POSITIVE_VALUE",
            $dtoClassNameAsUpperSnakeCase,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()))
        );

        return new ClassConstantDefinition($constantName, $constantValue, VisibilityEnum::publicEnum());
    }
}