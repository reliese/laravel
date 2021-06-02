<?php

namespace Reliese\Generator\DataAccess;

use Exception;
use Illuminate\Support\Str;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Blueprint\IndexBlueprint;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Database\WithPhpTypeMap;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\DataMap\WithModelDataMapClassAccessorGenerator;
use Reliese\Generator\DataMap\WithModelDataMapClassGenerator;
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
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Definition\CommentBlockStatementDefinition;
use Reliese\MetaCode\Definition\FunctionParameterDefinition;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Definition\StatementBlockDefinition;
use Reliese\MetaCode\Definition\StatementDefinitionCollection;
use Reliese\MetaCode\Definition\TryBlockDefinition;
use Reliese\MetaCode\Enum\AbstractEnum;
use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Tool\ClassNameTool;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * Class DataAccessAbstractClassGenerator
 */
class DataAccessAbstractClassGenerator implements ColumnBasedCodeGeneratorInterface
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
    use WithDataTransportObjectClassGenerator;

    /** @var ClassPropertyDefinition[] */
    private array $generatedForeignKeyDtoPropertyDefinitions = [];

    protected function allowClassFileOverwrite(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()->getDataAccessGeneratorConfiguration()
            ->getGeneratedClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getDataAccessGeneratorConfiguration()
            ->getGeneratedClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getDataAccessGeneratorConfiguration()
            ->getGeneratedClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $abstractClassDefinition = new ClassDefinition(
            $this->getObjectTypeDefinition($columnOwner),
            AbstractEnum::abstractEnum()
        );

        $modelObjectTypeDefinition = $this->getModelClassGenerator()->getObjectTypeDefinition($columnOwner);

        $abstractClassDefinition
            # include the With<ModelType>DataMap trait
            ->addTrait(
                new ClassTraitDefinition(
                    $this->getModelDataMapClassAccessorGenerator()->generateModelDataMapAccessorTrait(
                        $this->getModelDataMapClassGenerator()->getObjectTypeDefinition($columnOwner)
                            ->getFullyQualifiedName()
                    )
                ->getFullyQualifiedName()))
            ->addConstant(new ClassConstantDefinition($this->getMapFromFailedConstantName($modelObjectTypeDefinition),
                $this->getMapFromFailedConstantName($modelObjectTypeDefinition),
                VisibilityEnum::protectedEnum()))
            ->addConstant(new ClassConstantDefinition($this->getMapToFailedConstantName($modelObjectTypeDefinition),
                $this->getMapToFailedConstantName($modelObjectTypeDefinition),
                VisibilityEnum::protectedEnum()))
        ;

        $this->addFetchByUniqueColumnMethods($columnOwner, $abstractClassDefinition);

        $abstractClassDefinition->addMethodDefinition(
            $this->generateCreateMethod($columnOwner, $abstractClassDefinition));

        $abstractClassDefinition->addMethodDefinition(
            $this->generateUpdateMethod($columnOwner, $abstractClassDefinition));

        return $abstractClassDefinition;
    }

    /**
     * @param ObjectTypeDefinition $modelObjectTypeDefinition
     *
     * @return string
     */
    private function getMapToFailedConstantName(ObjectTypeDefinition $modelObjectTypeDefinition): string
    {
        return sprintf('%s_MAP_TO_DTO_FAILED',
            Str::upper($modelObjectTypeDefinition->getImportableName()));
    }

    /**
     * @param ObjectTypeDefinition $modelObjectTypeDefinition
     *
     * @return string
     */
    private function getMapFromFailedConstantName(ObjectTypeDefinition $modelObjectTypeDefinition): string
    {
        return sprintf('%s_MAP_FROM_DTO_FAILED',
            Str::upper($modelObjectTypeDefinition->getImportableName()));
    }


    /**
     * Calls \Reliese\Generator\DataAccess\DataAccessGenerator::generateFetchByUniqueColumnMethodDefinition foreach
     * unique column
     *
     * @param ColumnOwnerInterface  $columnOwner
     * @param ClassDefinition $abstractClassDefinition
     */
    private function addFetchByUniqueColumnMethods(
        ColumnOwnerInterface $columnOwner,
        ClassDefinition $abstractClassDefinition
    ) {
        $uniqueColumnGroups = $columnOwner->getUniqueColumnGroups();

        foreach ($uniqueColumnGroups as $uniqueColumnGroup) {
            if (1 < count($uniqueColumnGroup)) {
                continue;
            }

            $uniqueColumn = array_pop($uniqueColumnGroup);

            $abstractClassDefinition->addMethodDefinition(
                $this->generateFetchByUniqueColumnMethodDefinition(
                    $abstractClassDefinition,
                    $columnOwner,
                    $uniqueColumn
                )
            );
        }
    }

    /**
     * @param ColumnOwnerInterface $columnOwner
     * @param ClassDefinition      $classDefinition
     *
     * @return ClassMethodDefinition
     */
    private function generateCreateMethod(
        ColumnOwnerInterface $columnOwner,
        ClassDefinition $classDefinition
    ): ClassMethodDefinition {

        $modelObjectTypeDefinition = $this->getModelClassGenerator()->getObjectTypeDefinition($columnOwner);

        $mySqlErrorTypesObjectTypeDefinition = new ObjectTypeDefinition('\app\DataStores\MySql\MySqlErrorTypes');

        $queryExceptionTypeDefinition = new ObjectTypeDefinition('\Illuminate\Database\QueryException');

        $logMessageObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogMessage');

        $logExceptionObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogException');

        $returnTypeObjectTypeDefinition
            = new ObjectTypeDefinition('\app\Patterns\MethodResponses\CreateMethodResponse');

        $dtoTypeObjectTypeDefinition
            = $this->getDataTransportObjectClassGenerator()->getObjectTypeDefinition($columnOwner);

        $modelDataMapTraitObjectTypeDefinition
            = $this->getModelDataMapClassAccessorGenerator()->getObjectTypeDefinition($columnOwner);

        $classDefinition
            ->addImport($modelObjectTypeDefinition)
            ->addImport($mySqlErrorTypesObjectTypeDefinition)
            ->addImport($logMessageObjectTypeDefinition)
            ->addImport($logExceptionObjectTypeDefinition)
            ->addImport($returnTypeObjectTypeDefinition)
            ->addImport($dtoTypeObjectTypeDefinition)
            ->addImport($modelDataMapTraitObjectTypeDefinition)
        ;

        $functionName = "create";
        $returnType = PhpTypeEnum::objectOfType($returnTypeObjectTypeDefinition->getFullyQualifiedName());
        $modelVariableName = ClassNameTool::classNameToVariableName($modelObjectTypeDefinition->getImportableName());

        $methodFailedConstantName = sprintf('%s_CREATE_FAILED',
            Str::upper($modelObjectTypeDefinition->getImportableName()));
        $classDefinition->addConstant(new ClassConstantDefinition($methodFailedConstantName,
            $methodFailedConstantName,
            VisibilityEnum::protectedEnum()));
        $dtoParameterDefinition = $this->getDtoFunctionParameterDefinition($dtoTypeObjectTypeDefinition);
        $exceptionVariableName = 'exception';

        /*
         * Build Unique Constraint Error Handling Conditions
         */
        $catchBlockStatements = new StatementDefinitionCollection();
        $catchBlockStatements->addStatementDefinition((new CommentBlockStatementDefinition())->addLine("Treat unique key errors as validation failures"))
            ->addStatementDefinition((new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (!empty(\$%s->errorInfo[1]) && %s::UNIQUE_KEY_VIOLATION_TYPE_ID === \$%s->errorInfo[1])",
                $exceptionVariableName,
                $mySqlErrorTypesObjectTypeDefinition->getImportableName(),
                $exceptionVariableName))))->addStatementDefinition($foreachBlock
                = (new StatementBlockDefinition(new RawStatementDefinition(sprintf("foreach (\$%s->errorInfo as \$value)",
                $exceptionVariableName))))))
        ;

        foreach ($columnOwner->getUniqueIndexes(false) as $uniqueIndexBlueprint) {
            $validationMessageMethod = $this->getUniqueKeyViolationValidationMessageMethod($classDefinition,
                $uniqueIndexBlueprint);

            $classDefinition->addMethodDefinition($validationMessageMethod);

            $foreachBlock->addStatementDefinition((new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (str_contains(\$value, '%s'))",
                $uniqueIndexBlueprint->getName()))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::invalid([\$this->%s()]);",
                $returnTypeObjectTypeDefinition->getImportableName(),
                $validationMessageMethod->getFunctionName()))));
        }

        $catchBlockStatements->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new %s(static::%s), new %s(\$%s),]);",
            $returnTypeObjectTypeDefinition->getImportableName(),
            $logMessageObjectTypeDefinition->getImportableName(),
            $methodFailedConstantName,
            $logExceptionObjectTypeDefinition->getImportableName(),
            $exceptionVariableName)));
        /*
         * Build Class Method definition
         */
        $classMethodDefinition = new ClassMethodDefinition($functionName, $returnType, [$dtoParameterDefinition]);
        $classMethodDefinition->appendBodyStatement(// new model statement
            new RawStatementDefinition(sprintf("\$%s = new %s();",
                $modelVariableName,
                $modelObjectTypeDefinition->getImportableName())))
            ->appendBodyStatement((new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (!\$this->%s()->from%s(\$%s, \$%s))",
                $this->getModelDataMapClassAccessorGenerator()->getModelMapAccessorTraitMethodName($columnOwner),
                $dtoTypeObjectTypeDefinition->getImportableName(),
                $modelVariableName,
                $dtoParameterDefinition->getParameterName()))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new %s(static::%s), new %s(static::%s)]);",
                $returnTypeObjectTypeDefinition->getImportableName(),
                $logMessageObjectTypeDefinition->getImportableName(),
                $this->getMapFromFailedConstantName($modelObjectTypeDefinition),
                $logMessageObjectTypeDefinition->getImportableName(),
                $methodFailedConstantName))))
            // try save block
            ->appendBodyStatement(
                $this->getSaveModelTryBlock($modelVariableName,
                    $returnTypeObjectTypeDefinition,
                    $logMessageObjectTypeDefinition,
                    $methodFailedConstantName,
                    $queryExceptionTypeDefinition,
                    $exceptionVariableName,
                    $catchBlockStatements))
            // after try block, map model to dto
            ->appendBodyStatement($this->getMapToDtoStatementBlock($columnOwner,
                $dtoTypeObjectTypeDefinition,
                $modelVariableName,
                $dtoParameterDefinition,
                $this->getMapToFailedConstantName($modelObjectTypeDefinition),
                $returnTypeObjectTypeDefinition))
            # return created
            ->appendBodyStatement(new RawStatementDefinition(sprintf("return %s::created();",
                $returnTypeObjectTypeDefinition->getImportableName())))
        ;

        return $classMethodDefinition;
    }

    private function generateUpdateMethod(
        ColumnOwnerInterface $columnOwner,
        ClassDefinition $classDefinition
    ): ClassMethodDefinition
    {
        $modelObjectTypeDefinition = $this->getModelClassGenerator()->getObjectTypeDefinition($columnOwner);

        $mySqlErrorTypesObjectTypeDefinition = new ObjectTypeDefinition('\app\DataStores\MySql\MySqlErrorTypes');

        $queryExceptionTypeDefinition = new ObjectTypeDefinition('\Illuminate\Database\QueryException');

        $logMessageObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogMessage');

        $logExceptionObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogException');

        $returnTypeObjectTypeDefinition
            = new ObjectTypeDefinition('\app\Patterns\MethodResponses\UpdateMethodResponse');

        $dtoTypeObjectTypeDefinition
            = $this->getDataTransportObjectClassGenerator()->getObjectTypeDefinition($columnOwner);

        $modelDataMapTraitObjectTypeDefinition = $this->getModelDataMapClassAccessorGenerator()->getObjectTypeDefinition($columnOwner);

        $classDefinition
            ->addImport($modelObjectTypeDefinition)
            ->addImport($mySqlErrorTypesObjectTypeDefinition)
            ->addImport($logMessageObjectTypeDefinition)
            ->addImport($logExceptionObjectTypeDefinition)
            ->addImport($returnTypeObjectTypeDefinition)
            ->addImport($dtoTypeObjectTypeDefinition)
            ->addImport($modelDataMapTraitObjectTypeDefinition)
        ;

        $functionName = "update";
        $returnType = PhpTypeEnum::objectOfType($returnTypeObjectTypeDefinition->getFullyQualifiedName());
        $modelVariableName = ClassNameTool::classNameToVariableName($modelObjectTypeDefinition->getImportableName());

        $methodFailedConstantName = sprintf('%s_UPDATE_FAILED',
            Str::upper($modelObjectTypeDefinition->getImportableName()));
        $classDefinition->addConstant(new ClassConstantDefinition($methodFailedConstantName,
            $methodFailedConstantName,
            VisibilityEnum::protectedEnum()));
        $dtoParameterDefinition = $this->getDtoFunctionParameterDefinition($dtoTypeObjectTypeDefinition);
        $exceptionVariableName = 'exception';

        /*
         * Build Unique Constraint Error Handling Conditions
         */
        $catchBlockStatements = new StatementDefinitionCollection();
        $catchBlockStatements->addStatementDefinition((new CommentBlockStatementDefinition())->addLine("Treat unique key errors as validation failures"))
            ->addStatementDefinition((new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (!empty(\$%s->errorInfo[1]) && %s::UNIQUE_KEY_VIOLATION_TYPE_ID === \$%s->errorInfo[1])",
                $exceptionVariableName,
                $mySqlErrorTypesObjectTypeDefinition->getImportableName(),
                $exceptionVariableName))))->addStatementDefinition($foreachBlock
                = (new StatementBlockDefinition(new RawStatementDefinition(sprintf("foreach (\$%s->errorInfo as \$value)",
                $exceptionVariableName))))))
        ;

        foreach ($columnOwner->getUniqueIndexes(false) as $uniqueIndexBlueprint) {
            $validationMessageMethod = $this->getUniqueKeyViolationValidationMessageMethod($classDefinition,
                $uniqueIndexBlueprint);

            $classDefinition->addMethodDefinition($validationMessageMethod);

            $foreachBlock->addStatementDefinition((new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (str_contains(\$value, '%s'))",
                $uniqueIndexBlueprint->getName()))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::invalid([\$this->%s()]);",
                $returnTypeObjectTypeDefinition->getImportableName(),
                $validationMessageMethod->getFunctionName()))));
        }

        $catchBlockStatements->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new %s(static::%s), new %s(\$%s),]);",
            $returnTypeObjectTypeDefinition->getImportableName(),
            $logMessageObjectTypeDefinition->getImportableName(),
            $methodFailedConstantName,
            $logExceptionObjectTypeDefinition->getImportableName(),
            $exceptionVariableName)));
        /*
         * Define Find Model by Dto Id try block
         */
        $findModelTryBlock = new TryBlockDefinition();
        $findModelTryBlock->addStatementDefinition(// find model statement
            new RawStatementDefinition(sprintf("\$%s = %s::find(\$%s->getId());",
                $modelVariableName,
                $modelObjectTypeDefinition->getImportableName(),
                $dtoParameterDefinition->getParameterName())))
            ->addCatchStatements(PhpTypeEnum::objectOfType(Exception::class),
                $exceptionVariableName,
                (new StatementDefinitionCollection())->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new LogMessage(static::%s), new LogException(\$%s),]);",
                    $returnTypeObjectTypeDefinition->getImportableName(),
                    $methodFailedConstantName,
                    $exceptionVariableName))))
        ;
        /*
         * Define if model->save() Try Block
         */
        $modelSaveTryBlock = new TryBlockDefinition();
        $modelSaveTryBlock
            ->addStatementDefinition(
                (new StatementBlockDefinition(
                    new RawStatementDefinition(
                        sprintf(
                            "if (!\$%s->save())",
                            $modelVariableName))))
                    ->addStatementDefinition(
                        new RawStatementDefinition(
                            sprintf(
                                "return %s::error([new %s(static::%s)]);",
                                $returnTypeObjectTypeDefinition->getImportableName(),
                                $logMessageObjectTypeDefinition->getImportableName(),
                                $methodFailedConstantName
                            )))
            );
        // if (!$model->save())
        /*
         * Build Class Method definition
         */
        $classMethodDefinition = new ClassMethodDefinition($functionName, $returnType, [$dtoParameterDefinition]);
        $classMethodDefinition
            // Add try find model
            ->appendBodyStatement($findModelTryBlock)
            // Add if model not found
            ->appendBodyStatement($this->getModelNotFoundStatementBlock($modelVariableName,
                $returnTypeObjectTypeDefinition))
            // Add map DTO to Model block
            ->appendBodyStatement($this->getMapFromDtoStatementBlock($columnOwner,
                $dtoTypeObjectTypeDefinition,
                $modelVariableName,
                $dtoParameterDefinition,
                $returnTypeObjectTypeDefinition,
                $logMessageObjectTypeDefinition,
                $modelObjectTypeDefinition,
                $methodFailedConstantName))
            // try save block
            ->appendBodyStatement(
                $this->getSaveModelTryBlock($modelVariableName,
                    $returnTypeObjectTypeDefinition,
                    $logMessageObjectTypeDefinition,
                    $methodFailedConstantName,
                    $queryExceptionTypeDefinition,
                    $exceptionVariableName,
                    $catchBlockStatements))
            // after try block, map model to dto
            ->appendBodyStatement($this->getMapToDtoStatementBlock($columnOwner,
                $dtoTypeObjectTypeDefinition,
                $modelVariableName,
                $dtoParameterDefinition,
                $this->getMapToFailedConstantName($modelObjectTypeDefinition),
                $returnTypeObjectTypeDefinition))
            # return updated
            ->appendBodyStatement(new RawStatementDefinition(sprintf("return %s::updated();",
                $returnTypeObjectTypeDefinition->getImportableName())))
        ;

        return $classMethodDefinition;
    }

    private function getDtoFunctionParameterDefinition(ObjectTypeDefinition $dtoTypeObjectTypeDefinition): FunctionParameterDefinition
    {
        return new FunctionParameterDefinition(
            $this->getDtoVariableName($dtoTypeObjectTypeDefinition),
            PhpTypeEnum::objectOfType($dtoTypeObjectTypeDefinition->getFullyQualifiedName())
        );
    }
    private function getDtoVariableName(ObjectTypeDefinition $dtoTypeObjectTypeDefinition): string
    {
        $name = $dtoTypeObjectTypeDefinition->getImportableName();
        return strtolower($name[0]) . substr($name, 1);
    }

    /**
     * Example Output
     * <code>
     * public function fetchByExternalKey(AccountDto $accountDto): FetchMethodResponse
     * {
     *     try {
     *         $account = Account::where(Account::EXTERNAL_KEY, $accountDto->getExternalKey())->first();
     *     } catch (\Exception $exception) {
     *         return FetchMethodResponse::error([new LogMessage(static::ACCOUNT_FETCH_BY_EXTERNAL_KEY_FAILED), new
     * LogException($exception),]);
     *     }
     *     if (!$account) {
     *         return FetchMethodResponse::notFound();
     *     }
     *     if (!$this->getAccountMap()->toAccountDto($account, $accountDto)) {
     *         return FetchMethodResponse::error([new LogMessage(self::ACCOUNT_MAP_TO_DTO_FAILED)]);
     *     }
     *     return FetchMethodResponse::found();
     * }
     * </code>
     *
     * @param ClassDefinition $classDefinition
     * @param ColumnOwnerInterface  $columnOwner
     * @param ColumnBlueprint $uniqueColumn
     *
     * @return ClassMethodDefinition
     */
    private function generateFetchByUniqueColumnMethodDefinition(ClassDefinition $classDefinition,
        ColumnOwnerInterface $columnOwner,
        ColumnBlueprint $uniqueColumn
    ): ClassMethodDefinition
    {
        $logMessageObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogMessage');

        $logExceptionObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogException');

        $returnTypeObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\MethodResponses\FetchMethodResponse');

        $dtoTypeObjectTypeDefinition
            = $this->getDataTransportObjectClassGenerator()->getObjectTypeDefinition($columnOwner);

        $modelObjectTypeDefinition = $this->getModelClassGenerator()->getObjectTypeDefinition($columnOwner);

        $modelDataMapTraitObjectTypeDefinition
            = $this->getModelDataMapClassAccessorGenerator()->getObjectTypeDefinition($columnOwner);

        $classDefinition
            ->addImport($logMessageObjectTypeDefinition)
            ->addImport($logExceptionObjectTypeDefinition)
            ->addImport($returnTypeObjectTypeDefinition)
            ->addImport($dtoTypeObjectTypeDefinition)
            ->addImport($modelObjectTypeDefinition)
            ->addImport($modelDataMapTraitObjectTypeDefinition)
        ;

        $columnCamelName = Str::studly($uniqueColumn->getColumnName());

        $modelVariableName = ClassNameTool::classNameToVariableName($modelObjectTypeDefinition->getImportableName());

        $dtoParameterDefinition = $this->getDtoFunctionParameterDefinition($dtoTypeObjectTypeDefinition);

        $classMethodDefinition = new ClassMethodDefinition($this->getFetchByUniqueColumnFunctionName($uniqueColumn),
            PhpTypeEnum::objectOfType($returnTypeObjectTypeDefinition->getFullyQualifiedName()),
            [$dtoParameterDefinition]);

        $methodFailedConstantName = sprintf('%s_FETCH_BY_%s_FAILED',
            Str::upper($modelObjectTypeDefinition->getImportableName()),
            Str::upper(Str::snake($columnCamelName)));

        $classDefinition
            # include <MODEL_TYPE>_FETCH_BY_<COLUMN>_FAILED constant
            ->addConstant(new ClassConstantDefinition($methodFailedConstantName,
                $methodFailedConstantName,
                VisibilityEnum::protectedEnum()));

        $exceptionVariableName = 'exception';

        $columnConstantDefinition = $this->getModelAbstractClassGenerator()
            ->generateColumnConstantDefinition($uniqueColumn);
        $classMethodDefinition->appendBodyStatement((new TryBlockDefinition())->addStatementDefinition(# include the Eloquent query to fetch the model by the unique column value
            new RawStatementDefinition(sprintf("\$%s = %s::where(%s::%s, \$%s->get%s())->first();",
                $modelVariableName,
                $modelObjectTypeDefinition->getImportableName(),
                $modelObjectTypeDefinition->getImportableName(),
                $columnConstantDefinition->getName(),
                $dtoParameterDefinition->getParameterName(),
                $columnCamelName)))
            ->addCatchStatements(PhpTypeEnum::objectOfType(Exception::class),
                $exceptionVariableName,
                (new StatementDefinitionCollection())->addStatementDefinition(new RawStatementDefinition(sprintf("return FetchMethodResponse::error([new LogMessage(static::%s), new LogException(\$%s),]);",
                    $methodFailedConstantName,
                    $exceptionVariableName)))))
            # include the conditional check to determine if it was found
            ->appendBodyStatement($this->getModelNotFoundStatementBlock($modelVariableName,
                $returnTypeObjectTypeDefinition))
            # include the conditional check to determine if it was mapped successfully
            ->appendBodyStatement($this->getMapToDtoStatementBlock($columnOwner,
                $dtoTypeObjectTypeDefinition,
                $modelVariableName,
                $dtoParameterDefinition,
                $this->getMapToFailedConstantName($modelObjectTypeDefinition),
                $returnTypeObjectTypeDefinition))
            ->appendBodyStatement(new RawStatementDefinition("return FetchMethodResponse::found();"))
        ;

        return $classMethodDefinition;
    }

    /**
     * @param ColumnBlueprint $uniqueColumn
     *
     * @return string
     */
    public function getFetchByUniqueColumnFunctionName(ColumnBlueprint $uniqueColumn): string
    {
        return "fetchBy" . Str::studly($uniqueColumn->getColumnName());
    }

    private function getUniqueKeyViolationValidationMessageMethod(ClassDefinition $classDefinition,
        IndexBlueprint $indexBlueprint): ClassMethodDefinition
    {
        $returnType = new ObjectTypeDefinition(TranslatableMessage::class);
        $classDefinition->addImport($returnType);

        $functionName = sprintf("getUniqueKeyValidationMessageFor%s",
            Str::studly($indexBlueprint->getName()));

        return new ClassMethodDefinition($functionName,
            PhpTypeEnum::objectOfType($returnType->getFullyQualifiedName()),
            [],
            VisibilityEnum::protectedEnum(),
            InstanceEnum::instanceEnum(),
            AbstractEnum::abstractEnum());
    }

    /**
     * @param string                        $modelVariableName
     * @param ObjectTypeDefinition          $returnTypeObjectTypeDefinition
     * @param ObjectTypeDefinition          $logMessageObjectTypeDefinition
     * @param string                        $methodFailedConstantName
     * @param ObjectTypeDefinition          $queryExceptionTypeDefinition
     * @param string                        $exceptionVariableName
     * @param StatementDefinitionCollection $catchBlockStatements
     *
     * @return TryBlockDefinition
     */
    private function getSaveModelTryBlock(string $modelVariableName,
        ObjectTypeDefinition $returnTypeObjectTypeDefinition,
        ObjectTypeDefinition $logMessageObjectTypeDefinition,
        string $methodFailedConstantName,
        ObjectTypeDefinition $queryExceptionTypeDefinition,
        string $exceptionVariableName,
        StatementDefinitionCollection $catchBlockStatements): TryBlockDefinition
    {
        return (new TryBlockDefinition())
            // if (!$model->save()
            ->addStatementDefinition((new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (!\$%s->save())",
                $modelVariableName))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new %s(static::%s)]);",
                $returnTypeObjectTypeDefinition->getImportableName(),
                $logMessageObjectTypeDefinition->getImportableName(),
                $methodFailedConstantName))))
            // begin catch block
            ->addCatchStatements(PhpTypeEnum::objectOfType($queryExceptionTypeDefinition->getFullyQualifiedName()),
                $exceptionVariableName,
                $catchBlockStatements)
            ;
    }

    /**
     * @param ColumnOwnerInterface        $columnOwner
     * @param ObjectTypeDefinition        $dtoTypeObjectTypeDefinition
     * @param string                      $modelVariableName
     * @param FunctionParameterDefinition $dtoParameterDefinition
     * @param string                      $mapToFailedConstantName
     * @param ObjectTypeDefinition        $returnObjectTypeDefinition
     *
     * @return StatementBlockDefinition
     */
    private function getMapToDtoStatementBlock(ColumnOwnerInterface $columnOwner,
        ObjectTypeDefinition $dtoTypeObjectTypeDefinition,
        string $modelVariableName,
        FunctionParameterDefinition $dtoParameterDefinition,
        string $mapToFailedConstantName,
        ObjectTypeDefinition $returnObjectTypeDefinition): StatementBlockDefinition
    {
        //"if (!$this->accountMap->toAccountDto($accountModel, $accountDto))"
        return (new StatementBlockDefinition(new RawStatementDefinition(sprintf(
            "if (!\$this->%s()->to%s(\$%s, \$%s))",
            $this->getModelDataMapClassAccessorGenerator()->getModelMapAccessorTraitMethodName($columnOwner),
            $dtoTypeObjectTypeDefinition->getImportableName(),
            $modelVariableName,
            $dtoParameterDefinition->getParameterName()))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new LogMessage(self::%s)]);",
            $returnObjectTypeDefinition->getImportableName(),
            $mapToFailedConstantName)));
    }

    /**
     * @param string               $modelVariableName
     * @param ObjectTypeDefinition $returnTypeObjectTypeDefinition
     *
     * @return StatementBlockDefinition
     */
    private function getModelNotFoundStatementBlock(string $modelVariableName,
        ObjectTypeDefinition $returnTypeObjectTypeDefinition): StatementBlockDefinition
    {
        return (new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (!\$%s)",
            $modelVariableName))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::notFound();",
            $returnTypeObjectTypeDefinition->getImportableName())));
    }

    /**
     * Example statement block output
     * <code>
     * if (!$this->getPersonMap()->fromPersonDto($person, $personDto)) {
     *   return UpdateMethodResponse::error(
     *     [new LogMessage(static::PERSON_MAP_FROM_DTO_FAILED), new LogMessage(static::PERSON_UPDATE_FAILED)]);
     * }
     * </code>
     *
     * @param ColumnOwnerInterface        $columnOwner
     * @param ObjectTypeDefinition        $dtoTypeObjectTypeDefinition
     * @param string                      $modelVariableName
     * @param FunctionParameterDefinition $dtoParameterDefinition
     * @param ObjectTypeDefinition        $returnTypeObjectTypeDefinition
     * @param ObjectTypeDefinition        $logMessageObjectTypeDefinition
     * @param ObjectTypeDefinition        $modelTypeObjectTypeDefinition
     * @param string                      $methodFailedConstantName
     *
     * @return StatementBlockDefinition
     */
    private function getMapFromDtoStatementBlock(ColumnOwnerInterface $columnOwner,
        ObjectTypeDefinition $dtoTypeObjectTypeDefinition,
        string $modelVariableName,
        FunctionParameterDefinition $dtoParameterDefinition,
        ObjectTypeDefinition $returnTypeObjectTypeDefinition,
        ObjectTypeDefinition $logMessageObjectTypeDefinition,
        ObjectTypeDefinition $modelTypeObjectTypeDefinition,
        string $methodFailedConstantName): StatementBlockDefinition
    {
        return (new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (!\$this->%s()->from%s(\$%s, \$%s))",
            $this->getModelDataMapClassAccessorGenerator()->getModelMapAccessorTraitMethodName($columnOwner),
            $dtoTypeObjectTypeDefinition->getImportableName(),
            $modelVariableName,
            $dtoParameterDefinition->getParameterName()))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new %s(static::%s), new %s(static::%s)]);",
            $returnTypeObjectTypeDefinition->getImportableName(),
            $logMessageObjectTypeDefinition->getImportableName(),
            $this->getMapFromFailedConstantName($modelTypeObjectTypeDefinition),
            $logMessageObjectTypeDefinition->getImportableName(),
            $methodFailedConstantName)));
    }

}