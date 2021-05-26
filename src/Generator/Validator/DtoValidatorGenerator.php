<?php

namespace Reliese\Generator\Validator;

use Illuminate\Support\Str;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\RelieseConfiguration;
use Reliese\Configuration\ValidatorGeneratorConfiguration;
use Reliese\Generator\ClassGeneratorInterface;
use Reliese\Generator\DataTransport\DataTransportObjectGenerator;
use Reliese\MetaCode\Definition\ClassConstantDefinition;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassFieldDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\CommentBlockStatementDefinition;
use Reliese\MetaCode\Definition\FunctionParameterDefinition;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Definition\StatementBlockDefinition;
use Reliese\MetaCode\Definition\StatementDefinitionCollection;
use Reliese\MetaCode\Definition\StatementDefinitionInterface;
use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Tool\ClassNameTool;
use function array_key_exists;
use function implode;
use function in_array;
use function sprintf;
use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
/**
 * Class ValidatorObjectGenerator
 */
class DtoValidatorGenerator implements ClassGeneratorInterface
{
    /**
     * @var DataTransportObjectGenerator
     */
    private DataTransportObjectGenerator $dataTransportObjectGenerator;

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
     * @var ValidatorGeneratorConfiguration
     */
    private ValidatorGeneratorConfiguration $validatorGeneratorConfiguration;

    /**
     * DtoValidatorGenerator constructor.
     *
     * @param RelieseConfiguration $relieseConfiguration
     */
    public function __construct(RelieseConfiguration $relieseConfiguration)
    {
        $this->validatorGeneratorConfiguration = $relieseConfiguration->getValidatorGeneratorConfiguration();
        $this->dataTransportObjectGenerator = new DataTransportObjectGenerator($relieseConfiguration);
    }

    public function generateClass(TableBlueprint $tableBlueprint): ClassDefinition
    {
        $classDefinition = new ClassDefinition(
            $this->getClassName($tableBlueprint),
            $this->getClassNamespace($tableBlueprint)
        );

        $classDefinition->setParentClass(
            $this->getFullyQualifiedAbstractClassName($tableBlueprint)
        );

        return $classDefinition;
    }

    public function generateAbstractClass(TableBlueprint $tableBlueprint): ClassDefinition
    {
        $dtoValidatorAbstractClassDefinition = new ClassDefinition(
            $this->getAbstractClassName($tableBlueprint),
            $this->getAbstractClassNamespace($tableBlueprint)
        );

        $dtoAbstractClassDefinition = $this->dataTransportObjectGenerator
            ->generateAbstractDataTransportObjectClassDefinition($tableBlueprint);

        $dtoClassDefinition = $this->dataTransportObjectGenerator
            ->generateDataTransportObjectClassDefinition($tableBlueprint);

        $dtoValidatorAbstractClassDefinition->addImport($dtoClassDefinition);

        $this->addRequireAndValidateMethods($dtoAbstractClassDefinition,
            $dtoValidatorAbstractClassDefinition,
            $dtoClassDefinition
        );

        $this->addFkValidations(
            $dtoAbstractClassDefinition,
            $dtoValidatorAbstractClassDefinition,
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
        //            $dtoValidatorAbstractClassDefinition->addMethodDefinition($requireFieldMethodDefinition);

        return $dtoValidatorAbstractClassDefinition;
    }


    public function getModelClassDirectory() : string
    {
        return $this->validatorGeneratorConfiguration->getPath();
    }

    public function getModelClassFilePath(ClassDefinition $classDefinition): string
    {
        return $this->getModelClassDirectory(). DIRECTORY_SEPARATOR . $classDefinition->getClassName() . '.php';
    }

    public function getAbstractModelClassDirectory() : string
    {
        return $this->validatorGeneratorConfiguration->getPath(). DIRECTORY_SEPARATOR . 'Generated';
    }

    public function getAbstractModelClassFilePath(ClassDefinition $classDefinition): string
    {
        return $this->getAbstractModelClassDirectory(). DIRECTORY_SEPARATOR . $classDefinition->getClassName() . '.php';
    }

    public function getFullyQualifiedClassName(ColumnOwnerInterface $columnOwner): string
    {
        return $this->getClassNamespace($columnOwner).'\\'.$this->getClassName($columnOwner);
    }

    public function getClassNamespace(ColumnOwnerInterface $columnOwner): string
    {
        return $this->validatorGeneratorConfiguration->getNamespace();
    }

    public function getClassName(ColumnOwnerInterface $columnOwner): string
    {
        return ClassNameTool::snakeCaseToClassName(
            null,
            $columnOwner->getName(),
            $this->validatorGeneratorConfiguration->getClassSuffix()
        );
    }

    private function getFullyQualifiedAbstractClassName(ColumnOwnerInterface $columnOwner): string
    {
        return $this->getAbstractClassNamespace($columnOwner).'\\'.$this->getAbstractClassName($columnOwner);
    }

    private function getAbstractClassName(ColumnOwnerInterface $columnOwner): string
    {
        return $this->validatorGeneratorConfiguration->getParentClassPrefix()
            . $this->getClassName($columnOwner);
    }

    private function getAbstractClassNamespace(ColumnOwnerInterface $columnOwner): string
    {
        return $this->getClassNamespace($columnOwner) .'\\Generated';
    }

    private function generateRequireValueMethod(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition
    ): ClassMethodDefinition {
        if (array_key_exists($dtoPropertyDefinition->getVariableName(), $this->generatedRequireValueMethods)) {
            return $this->generatedRequireValueMethods[$dtoPropertyDefinition->getVariableName()];
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

        $classMethodDefinition = new ClassMethodDefinition(
            $this->getRequireValueMethodName($dtoPropertyDefinition),
            $this->getValidateMethodResponsePhpTypeEnum(),
            [$valueParameter]
        );

        $classMethodDefinition
            ->appendBodyStatement($ifEmptyStatement)
            ->appendBodyStatement($returnValidationResultStatement)
        ;

        return $this->generatedRequireValueMethods[$dtoPropertyDefinition->getVariableName()]
            = $classMethodDefinition;
    }

    private function generateValidateValueMethod(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition
    ): ClassMethodDefinition
    {
        if (array_key_exists($dtoPropertyDefinition->getVariableName(), $this->generatedValidateValueMethods)) {
            return $this->generatedValidateValueMethods[$dtoPropertyDefinition->getVariableName()];
        }

        $valueParameter = new FunctionParameterDefinition($dtoPropertyDefinition->getVariableName(), PhpTypeEnum::mixedType());

        $dtoValidatorAbstractClassDefinition
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

        return $this->generatedValidateValueMethods[$dtoPropertyDefinition->getVariableName()]
            = $classMethodDefinition;
    }

    private function generateValidateDtoPropertyMethod(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition
    ) : ClassMethodDefinition {

        if (array_key_exists($dtoPropertyDefinition->getVariableName(), $this->generatedValidateDtoPropertyMethods)) {
            return $this->generatedValidateDtoPropertyMethods[$dtoPropertyDefinition->getVariableName()];
        }

        $functionName = $this->getValidateDtoPropertyMethodName($dtoPropertyDefinition);
        $dtoParameter = $this->getNullableDtoFunctionParameterDefinition($dtoClassDefinition);
        $returnPhpEnumType = $this->getValidateMethodResponsePhpTypeEnum();

        $dtoValidatorAbstractClassDefinition
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

        return $this->generatedValidateDtoPropertyMethods[$dtoPropertyDefinition->getVariableName()] = $validateDtoFunction;
    }

    private function generateRequireDtoPropertyMethod(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition
    ) : ClassMethodDefinition {

        if (array_key_exists($dtoPropertyDefinition->getVariableName(), $this->generatedRequireDtoPropertyMethods)) {
            return $this->generatedRequireDtoPropertyMethods[$dtoPropertyDefinition->getVariableName()];
        }

        $functionName = $this->getRequireDtoPropertyMethodName($dtoPropertyDefinition);
        $dtoParameter = $this->getNullableDtoFunctionParameterDefinition($dtoClassDefinition);
        $returnPhpEnumType = $this->getRequireMethodResponsePhpTypeEnum();

        $dtoValidatorAbstractClassDefinition
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

        return $this->generatedRequireDtoPropertyMethods[$dtoPropertyDefinition->getVariableName()] = $requireDtoFunction;
    }

    private function getRequireDtoPropertyMethodName(
        ClassPropertyDefinition $dtoPropertyDefinition
    ): string {
        return sprintf(
            "requireDto%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

    private function getRequireDtoMethodName(
        ClassPropertyDefinition $dtoPropertyDefinition
    ): string {
        return sprintf(
            "require%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

    private function getRequireValueMethodName(
        ClassPropertyDefinition $dtoPropertyDefinition
    ): string {
        return sprintf(
            "require%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

    private function getNullableDtoFunctionParameterDefinition(ClassDefinition $dtoClassDefinition): FunctionParameterDefinition
    {
        return new FunctionParameterDefinition(
            ClassNameTool::dtoClassNameToVariableName($dtoClassDefinition->getClassName()),
            PhpTypeEnum::nullableObjectOfType($dtoClassDefinition->getFullyQualifiedName())
        );
    }
    private function getDtoFunctionParameterDefinition(ClassDefinition $dtoClassDefinition): FunctionParameterDefinition
    {
        return new FunctionParameterDefinition(
            ClassNameTool::dtoClassNameToVariableName($dtoClassDefinition->getClassName()),
            PhpTypeEnum::objectOfType($dtoClassDefinition->getFullyQualifiedName())
        );
    }

    private function getRequireMethodResponsePhpTypeEnum(): PhpTypeEnum
    {
        return PhpTypeEnum::objectOfType($this->getRequireMethodResponseObjectTypeDefinition()
            ->getFullyQualifiedName());
    }

    private function getRequireMethodResponseObjectTypeDefinition(): ObjectTypeDefinition
    {
        return new ObjectTypeDefinition('\app\Patterns\MethodResponses\ValidateMethodResponse');
    }

    private function getValidateMethodResponsePhpTypeEnum(): PhpTypeEnum
    {
        return PhpTypeEnum::objectOfType($this->getValidateMethodResponseObjectTypeDefinition()->getFullyQualifiedName());
    }

    private function getValidateMethodResponseObjectTypeDefinition(): ObjectTypeDefinition
    {
        return new ObjectTypeDefinition('\app\Patterns\MethodResponses\ValidateMethodResponse');
    }

    private function getReturnIsValidStatement(): StatementDefinitionInterface
    {
        return new RawStatementDefinition(
            sprintf(
                "return %s::valid();",
                $this->getValidateMethodResponseObjectTypeDefinition()->getImportableName())
        );
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
        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getTableBlueprint()->getName())));
        $dtoClassNameAsUpperSnakeCase = Str::upper(Str::singular($dtoClassDefinition->getTableBlueprint()->getName()));
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
     * @param ClassDefinition         $dtoClassDefinition
     * @param ClassPropertyDefinition $dtoPropertyDefinition
     *
     * @return ClassConstantDefinition
     */
    private function generatePropertyMaxLengthValidationMessageConstant(
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
    ): ClassConstantDefinition {
        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getTableBlueprint()->getName())));
        $dtoClassNameAsUpperSnakeCase = Str::upper(Str::singular($dtoClassDefinition->getTableBlueprint()->getName()));

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
        return new ClassConstantDefinition($constantName, $constantValue);
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
        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getTableBlueprint()->getName())));
        $dtoClassNameAsUpperSnakeCase = Str::upper(Str::singular($dtoClassDefinition->getTableBlueprint()->getName()));
        /*
         * Create required message constant
         */
        $constantName = sprintf("%s_%s_POSITIVE_VALUE",
            $tableName,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()))
        );

        $constantValue = sprintf("VALIDATION.%s.%s.POSITIVE_VALUE",
            $dtoClassNameAsUpperSnakeCase,
            Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()))
        );

        return new ClassConstantDefinition($constantName, $constantValue);
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

        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getTableBlueprint()->getName())));

        $dtoClassNameAsUpperSnakeCase = Str::upper(Str::singular($dtoClassDefinition->getTableBlueprint()->getName()));

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
        return new ClassConstantDefinition($constantName, $constantValue);
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

        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getTableBlueprint()->getName())));

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

    private function getTranslatableMessageObjectTypeDefinition() : ObjectTypeDefinition
    {
        return new ObjectTypeDefinition('\Symfony\Component\Translation\TranslatableMessage');
    }

    private function generateRequireIfEmptyStatement(
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
        string $valueStatement
    ): StatementDefinitionInterface {

        $ifEmptyStatement = new RawStatementDefinition("if (\\empty($valueStatement))");

        return (new StatementBlockDefinition($ifEmptyStatement))
            ->addStatementDefinition(
                $this->generateReturnInvalidWithPropertyRequiredMessage(
                    $dtoValidatorAbstractClassDefinition,
                    $dtoClassDefinition,
                    $dtoPropertyDefinition
                )
            );
    }

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

    private function getValidateValueMethodName(ClassPropertyDefinition $dtoPropertyDefinition)
    {
        return sprintf(
            "validate%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

    private function getValidateDtoPropertyMethodName(ClassPropertyDefinition $dtoPropertyDefinition)
    {
        return sprintf(
            "validateDto%s", Str::Studly($dtoPropertyDefinition->getVariableName())
        );
    }

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
                    sprintf("if (\is_null(\$%s))", $propertyValueParameter->getParameterName())
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
                    sprintf("if (empty(\$%s))", $propertyValueParameter->getParameterName())
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

        if ($dtoClassDefinition->getClassName() === 'AccountDto') {
            echo sprintf("%s: is a %s\n",
                $dtoPropertyDefinition->getColumnBlueprint()
                    ->getUniqueName(),
                $dtoPropertyDefinition->getColumnBlueprint()
                    ->getDataType());
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

        $ifStatement = new RawStatementDefinition(sprintf("if (0 < %s)", $valueToTest));

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
     * @param ClassDefinition $dtoAbstractClassDefinition
     * @param ClassDefinition $dtoValidatorAbstractClassDefinition
     * @param ClassDefinition $dtoClassDefinition
     */
    private function addRequireAndValidateMethods(ClassDefinition $dtoAbstractClassDefinition,
        ClassDefinition $dtoValidatorAbstractClassDefinition,
        ClassDefinition $dtoClassDefinition): void
    {
        foreach ($dtoAbstractClassDefinition->getProperties() as $dtoPropertyDefinition) {
            if (!$dtoPropertyDefinition->hasColumnBlueprint()) {
                // unable to determine validation method w/o column blueprint
                continue;
            }

            // Apply configuration filters to columns
            if ($this->isExcludedColumn($dtoPropertyDefinition->getColumnBlueprint())) {
                continue;
            }

            $dtoValidatorAbstractClassDefinition->addMethodDefinition($this->generateRequireDtoPropertyMethod($dtoValidatorAbstractClassDefinition,
                $dtoClassDefinition,
                $dtoPropertyDefinition));

            $dtoValidatorAbstractClassDefinition->addMethodDefinition($this->generateValidateDtoPropertyMethod($dtoValidatorAbstractClassDefinition,
                $dtoClassDefinition,
                $dtoPropertyDefinition));

            $dtoValidatorAbstractClassDefinition->addMethodDefinition($this->generateRequireValueMethod($dtoValidatorAbstractClassDefinition,
                $dtoClassDefinition,
                $dtoPropertyDefinition));

            $dtoValidatorAbstractClassDefinition->addMethodDefinition($this->generateValidateValueMethod($dtoValidatorAbstractClassDefinition,
                $dtoClassDefinition,
                $dtoPropertyDefinition));
        }
    }

    private function isExcludedColumn(ColumnBlueprint $columnBlueprint): bool
    {
        return $this->validatorGeneratorConfiguration->getSchemaFilter()->isExcludedColumn(
            $columnBlueprint->getColumnOwner()->getSchemaBlueprint()->getSchemaName(),
            $columnBlueprint->getColumnOwner()->getName(),
            $columnBlueprint->getColumnName()
        );
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



            $methodDefinition = new ClassMethodDefinition(
                $this->getRequireDtoMethodName($dtoPropertyDefinition),
                $this->getValidateMethodResponsePhpTypeEnum(),
                [$dtoParameter]
            );
            $methodDefinition
                ->appendBodyStatement($this)
                ->appendBodyStatement($this->getReturnIsValidStatement());

            $dtoValidatorAbstractClassDefinition
                ->addConstant($requireDtoValidationMessageIndexConstant)
                ->addMethodDefinition($methodDefinition)
            ;
        }
    }

    private function generateRequireDtoValidationMessageConstant(
        ClassDefinition $dtoClassDefinition,
        ClassPropertyDefinition $dtoPropertyDefinition,
    ) {
        $tableName = Str::upper(Str::snake(Str::singular($dtoClassDefinition->getTableBlueprint()->getName())));
        $propertyNameUpperSnakeCase = Str::upper(Str::snake($dtoPropertyDefinition->getVariableName()));

        $constantName = sprintf("%s_%s_REQUIRED",
            $tableName,
            $propertyNameUpperSnakeCase
        );

        $constantValue = sprintf(
            "VALIDATION.%s.%s.REQUIRED",
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
}