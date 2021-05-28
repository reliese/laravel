<?php

namespace Reliese\Generator\DataAccess;

use app\DataStores\MySql\MySqlErrorTypes;
use app\DataTransport\Objects\PrimaryDatabase\AccountDto;
use App\Models\PrimaryDatabase\Account;
use app\Patterns\Log\LogException;
use app\Patterns\Log\LogMessage;
use app\Patterns\MethodResponses\CreateMethodResponse;
use app\Patterns\MethodResponses\FetchMethodResponse;
use app\Patterns\MethodResponses\GetMethodResponse;
use app\Patterns\MethodResponses\UpdateMethodResponse;
use Illuminate\Support\Str;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\IndexBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\DataAccessGeneratorConfiguration;
use Reliese\Configuration\RelieseConfiguration;
use Reliese\Generator\DataAttribute\DataAttributeGenerator;
use Reliese\Generator\DataMap\ModelDataMapGenerator;
use Reliese\Generator\DataTransport\DataTransportObjectGenerator;
use Reliese\Generator\Model\ModelGenerator;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\ClassConstantDefinition;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
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
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;

/**
 * Class DataAccessGenerator
 */
class DataAccessGenerator
{
    /**
     * @var DataAccessGeneratorConfiguration
     */
    private DataAccessGeneratorConfiguration $dataAccessGeneratorConfiguration;

    /**
     * @var DataAttributeGenerator
     */
    private DataAttributeGenerator $dataAttributeGenerator;

    /**
     * @var DataTransportObjectGenerator
     */
    private DataTransportObjectGenerator $dataTransportObjectGenerator;

    /**
     * @var MySqlDataTypeMap
     */
    private MySqlDataTypeMap $dataTypeMap;

    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    /**
     * @var ModelDataMapGenerator
     */
    private ModelDataMapGenerator $modelDataMapGenerator;

    /**
     * @var ModelGenerator
     */
    private ModelGenerator $modelGenerator;

    /**
     * DataAccessGenerator constructor.
     *
     * @param RelieseConfiguration $relieseConfiguration
     */
    public function __construct(RelieseConfiguration $relieseConfiguration)
    {
        $this->dataAccessGeneratorConfiguration = $relieseConfiguration->getDataAccessGeneratorConfiguration();
        /*
         * TODO: inject a MySql / Postgress or other DataType mapping as needed
         */
        $this->dataTypeMap = new MySqlDataTypeMap();
        $this->modelGenerator = new ModelGenerator($relieseConfiguration);
        $this->dataTransportObjectGenerator = new DataTransportObjectGenerator($relieseConfiguration);
        $this->modelDataMapGenerator = new ModelDataMapGenerator($relieseConfiguration);
    }

    /**
     * @param TableBlueprint $tableBlueprint
     */
    public function fromTableBlueprint(TableBlueprint $tableBlueprint)
    {
        $classDefinition = $this->generateClassDefinition($tableBlueprint);
        $abstractClassDefinition = $this->generateAbstractClassDefinition($tableBlueprint);

        /*
         * TODO: Add generic methods like "get by id"
         */

        /*
         * Write the Class Files
         */
        $this->writeClassFiles($classDefinition, $abstractClassDefinition);
    }

    public function generateClassDefinition(TableBlueprint $tableBlueprint): ClassDefinition
    {
        $abstractClassDefinition = $this->generateAbstractClassDefinition($tableBlueprint);

        $className = $this->getClassName($tableBlueprint);
        $namespace = $this->getClassNamespace($tableBlueprint);
        $classDefinition = new ClassDefinition($className, $namespace);
        $classDefinition->setParentClass($abstractClassDefinition->getFullyQualifiedName());

        return $classDefinition;
    }

    public function generateAbstractClassDefinition(TableBlueprint $tableBlueprint): ClassDefinition
    {
        $abstractClassName = $this->getAbstractClassName($tableBlueprint);
        $abstractNamespace = $this->getAbstractClassNamespace($tableBlueprint);
        $abstractClassDefinition = new ClassDefinition($abstractClassName,
            $abstractNamespace,
            AbstractEnum::abstractEnum());

        $modelObjectTypeDefinition
            = new ObjectTypeDefinition($this->modelGenerator->getFullyQualifiedClassName($tableBlueprint));

        $abstractClassDefinition
            # include the With<ModelType>DataMap trait
            ->addTrait(new ClassTraitDefinition($this->modelDataMapGenerator->generateModelDataMapAccessorTrait($this->modelDataMapGenerator->getFullyQualifiedClassName($tableBlueprint))
                ->getFullyQualifiedName()))
            ->addConstant(new ClassConstantDefinition($this->getMapFromFailedConstantName($modelObjectTypeDefinition),
                $this->getMapFromFailedConstantName($modelObjectTypeDefinition),
                VisibilityEnum::protectedEnum()),)
            ->addConstant(new ClassConstantDefinition($this->getMapToFailedConstantName($modelObjectTypeDefinition),
                $this->getMapToFailedConstantName($modelObjectTypeDefinition),
                VisibilityEnum::protectedEnum()))
        ;

        $this->addFetchByUniqueColumnMethods($tableBlueprint, $abstractClassDefinition);

        $abstractClassDefinition->addMethodDefinition($this->generateCreateMethod($tableBlueprint,
            $abstractClassDefinition));

        $abstractClassDefinition->addMethodDefinition($this->generateUpdateMethod($tableBlueprint,
            $abstractClassDefinition));

        return $abstractClassDefinition;
    }

    /**
     * @param TableBlueprint $tableBlueprint
     *
     * @return string
     */
    public function getFullyQualifiedClassName(TableBlueprint $tableBlueprint): string
    {
        return $this->getClassNamespace($tableBlueprint) . '\\' . $this->getClassName($tableBlueprint);
    }

    public function getClassNamespace(TableBlueprint $tableBlueprint): string
    {
        return $this->dataAccessGeneratorConfiguration->getNamespace();
    }

    public function getClassName(TableBlueprint $tableBlueprint): string
    {
        return ClassNameTool::snakeCaseToClassName($this->dataAccessGeneratorConfiguration->getClassPrefix(),
            $tableBlueprint->getName(),
            $this->dataAccessGeneratorConfiguration->getClassSuffix());
    }

    public function getFetchByUniqueColumnFunctionName(ColumnBlueprint $uniqueColumn): string
    {
        return "fetchBy" . Str::studly($uniqueColumn->getColumnName());
    }

    private function generateCreateMethod(TableBlueprint $tableBlueprint,
        ClassDefinition $classDefinition): ClassMethodDefinition
    {
        $modelObjectTypeDefinition
            = new ObjectTypeDefinition($this->modelGenerator->getFullyQualifiedClassName($tableBlueprint));
        $mySqlErrorTypesObjectTypeDefinition = new ObjectTypeDefinition('\app\DataStores\MySql\MySqlErrorTypes');
        $queryExceptionTypeDefinition = new ObjectTypeDefinition('\Illuminate\Database\QueryException');
        $logMessageObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogMessage');
        $logExceptionObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogException');
        $returnTypeObjectTypeDefinition
            = new ObjectTypeDefinition('\app\Patterns\MethodResponses\CreateMethodResponse');
        $dtoTypeObjectTypeDefinition
            = new ObjectTypeDefinition($this->dataTransportObjectGenerator->getFullyQualifiedClassName($tableBlueprint));
        $modelTypeObjectTypeDefinition
            = new ObjectTypeDefinition($this->modelGenerator->getFullyQualifiedClassName($tableBlueprint));
        $modelDataMapTraitObjectTypeDefinition
            = new ObjectTypeDefinition($this->modelDataMapGenerator->generateModelDataMapAccessorTrait($this->modelDataMapGenerator->getFullyQualifiedClassName($tableBlueprint))
            ->getFullyQualifiedName());
        $classDefinition->addImport($modelObjectTypeDefinition)
            ->addImport($mySqlErrorTypesObjectTypeDefinition)
            ->addImport($logMessageObjectTypeDefinition)
            ->addImport($logExceptionObjectTypeDefinition)
            ->addImport($returnTypeObjectTypeDefinition)
            ->addImport($dtoTypeObjectTypeDefinition)
            ->addImport($modelTypeObjectTypeDefinition)
            ->addImport($modelDataMapTraitObjectTypeDefinition)
        ;

        $functionName = "create";
        $returnType = PhpTypeEnum::objectOfType($returnTypeObjectTypeDefinition->getFullyQualifiedName());
        $modelVariableName = $this->modelGenerator->getClassAsVariableName($tableBlueprint);

        $methodFailedConstantName = sprintf('%s_CREATE_FAILED',
            Str::upper($modelTypeObjectTypeDefinition->getImportableName()));
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
                $exceptionVariableName,))))))
        ;

        foreach ($tableBlueprint->getUniqueIndexes(false) as $uniqueIndexBlueprint) {
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
                $modelTypeObjectTypeDefinition->getImportableName())))
            ->appendBodyStatement((new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (!\$this->%s()->from%s(\$%s, \$%s))",
                $this->modelDataMapGenerator->getModelMapAccessorTraitMethodName($tableBlueprint),
                $dtoTypeObjectTypeDefinition->getImportableName(),
                $modelVariableName,
                $dtoParameterDefinition->getParameterName()))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new %s(static::%s), new %s(static::%s)]);",
                $returnTypeObjectTypeDefinition->getImportableName(),
                $logMessageObjectTypeDefinition->getImportableName(),
                $this->getMapFromFailedConstantName($modelTypeObjectTypeDefinition),
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
            ->appendBodyStatement($this->getMapToDtoStatementBlock($tableBlueprint,
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

    private function generateUpdateMethod(TableBlueprint $tableBlueprint,
        ClassDefinition $classDefinition): ClassMethodDefinition
    {
        $modelObjectTypeDefinition
            = new ObjectTypeDefinition($this->modelGenerator->getFullyQualifiedClassName($tableBlueprint));
        $mySqlErrorTypesObjectTypeDefinition = new ObjectTypeDefinition('\app\DataStores\MySql\MySqlErrorTypes');
        $queryExceptionTypeDefinition = new ObjectTypeDefinition('\Illuminate\Database\QueryException');
        $logMessageObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogMessage');
        $logExceptionObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogException');
        $returnTypeObjectTypeDefinition
            = new ObjectTypeDefinition('\app\Patterns\MethodResponses\UpdateMethodResponse');
        $dtoTypeObjectTypeDefinition
            = new ObjectTypeDefinition($this->dataTransportObjectGenerator->getFullyQualifiedClassName($tableBlueprint));
        $modelTypeObjectTypeDefinition
            = new ObjectTypeDefinition($this->modelGenerator->getFullyQualifiedClassName($tableBlueprint));
        $modelDataMapTraitObjectTypeDefinition
            = new ObjectTypeDefinition($this->modelDataMapGenerator->generateModelDataMapAccessorTrait($this->modelDataMapGenerator->getFullyQualifiedClassName($tableBlueprint))
            ->getFullyQualifiedName());
        $classDefinition->addImport($modelObjectTypeDefinition)
            ->addImport($mySqlErrorTypesObjectTypeDefinition)
            ->addImport($logMessageObjectTypeDefinition)
            ->addImport($logExceptionObjectTypeDefinition)
            ->addImport($returnTypeObjectTypeDefinition)
            ->addImport($dtoTypeObjectTypeDefinition)
            ->addImport($modelTypeObjectTypeDefinition)
            ->addImport($modelDataMapTraitObjectTypeDefinition)
        ;

        $functionName = "update";
        $returnType = PhpTypeEnum::objectOfType($returnTypeObjectTypeDefinition->getFullyQualifiedName());
        $modelVariableName = $this->modelGenerator->getClassAsVariableName($tableBlueprint);

        $methodFailedConstantName = sprintf('%s_UPDATE_FAILED',
            Str::upper($modelTypeObjectTypeDefinition->getImportableName()));
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
                $exceptionVariableName,))))))
        ;

        foreach ($tableBlueprint->getUniqueIndexes(false) as $uniqueIndexBlueprint) {
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
                $modelTypeObjectTypeDefinition->getImportableName(),
                $dtoParameterDefinition->getParameterName())))
            ->addCatchStatements(PhpTypeEnum::objectOfType(\Exception::class),
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
            ->appendBodyStatement($this->getMapFromDtoStatementBlock($tableBlueprint,
                $dtoTypeObjectTypeDefinition,
                $modelVariableName,
                $dtoParameterDefinition,
                $returnTypeObjectTypeDefinition,
                $logMessageObjectTypeDefinition,
                $modelTypeObjectTypeDefinition,
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
            ->appendBodyStatement($this->getMapToDtoStatementBlock($tableBlueprint,
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

    /**
     * Calls \Reliese\Generator\DataAccess\DataAccessGenerator::generateFetchByUniqueColumnMethodDefinition foreach
     * unique column
     *
     * @param TableBlueprint  $tableBlueprint
     * @param ClassDefinition $abstractClassDefinition
     *
     * @return array
     */
    private function addFetchByUniqueColumnMethods(TableBlueprint $tableBlueprint,
        ClassDefinition $abstractClassDefinition)
    {
        $uniqueColumnGroups = $tableBlueprint->getUniqueColumnGroups();

        foreach ($uniqueColumnGroups as $uniqueColumnGroup) {
            if (1 < count($uniqueColumnGroup)) {
                continue;
            }

            $uniqueColumn = array_pop($uniqueColumnGroup);

            $abstractClassDefinition->addMethodDefinition($this->generateFetchByUniqueColumnMethodDefinition($abstractClassDefinition,
                $tableBlueprint,
                $uniqueColumn));
        }
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
     * @param TableBlueprint  $tableBlueprint
     * @param ColumnBlueprint $uniqueColumn
     *
     * @return ClassMethodDefinition
     */
    private function generateFetchByUniqueColumnMethodDefinition(ClassDefinition $classDefinition,
        TableBlueprint $tableBlueprint,
        ColumnBlueprint $uniqueColumn): ClassMethodDefinition
    {
        $logMessageObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogMessage');
        $logExceptionObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\Log\LogException');
        $returnTypeObjectTypeDefinition = new ObjectTypeDefinition('\app\Patterns\MethodResponses\FetchMethodResponse');
        $dtoTypeObjectTypeDefinition
            = new ObjectTypeDefinition($this->dataTransportObjectGenerator->getFullyQualifiedClassName($tableBlueprint));
        $modelObjectTypeDefinition
            = new ObjectTypeDefinition($this->modelGenerator->getFullyQualifiedClassName($tableBlueprint));
        $modelDataMapTraitObjectTypeDefinition
            = new ObjectTypeDefinition($this->modelDataMapGenerator->generateModelDataMapAccessorTrait($this->modelDataMapGenerator->getFullyQualifiedClassName($tableBlueprint))
            ->getFullyQualifiedName());
        $classDefinition->addImport($logMessageObjectTypeDefinition)
            ->addImport($logExceptionObjectTypeDefinition)
            ->addImport($returnTypeObjectTypeDefinition)
            ->addImport($dtoTypeObjectTypeDefinition)
            ->addImport($modelObjectTypeDefinition)
            ->addImport($modelDataMapTraitObjectTypeDefinition)
        ;

        $columnCamelName = Str::studly($uniqueColumn->getColumnName());

        $modelVariableName = $this->modelGenerator->getClassAsVariableName($tableBlueprint);

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

        $columnConstantDefinition = $this->modelGenerator->generateColumnConstantDefinition($uniqueColumn);
        $classMethodDefinition->appendBodyStatement((new TryBlockDefinition())->addStatementDefinition(# include the Eloquent query to fetch the model by the unique column value
            new RawStatementDefinition(sprintf("\$%s = %s::where(%s::%s, \$%s->get%s())->first();",
                $modelVariableName,
                $modelObjectTypeDefinition->getImportableName(),
                $modelObjectTypeDefinition->getImportableName(),
                $columnConstantDefinition->getName(),
                $dtoParameterDefinition->getParameterName(),
                $columnCamelName)))
            ->addCatchStatements(PhpTypeEnum::objectOfType(\Exception::class),
                $exceptionVariableName,
                (new StatementDefinitionCollection())->addStatementDefinition(new RawStatementDefinition(sprintf("return FetchMethodResponse::error([new LogMessage(static::%s), new LogException(\$%s),]);",
                    $methodFailedConstantName,
                    $exceptionVariableName)))))
            # include the conditional check to determine if it was found
            ->appendBodyStatement($this->getModelNotFoundStatementBlock($modelVariableName,
                $returnTypeObjectTypeDefinition))
            # include the conditional check to determine if it was mapped successfully
            ->appendBodyStatement($this->getMapToDtoStatementBlock($tableBlueprint,
                $dtoTypeObjectTypeDefinition,
                $modelVariableName,
                $dtoParameterDefinition,
                $this->getMapToFailedConstantName($modelObjectTypeDefinition),
                $returnTypeObjectTypeDefinition))
            ->appendBodyStatement(new RawStatementDefinition("return FetchMethodResponse::found();"))
        ;

        return $classMethodDefinition;
    }

    private function getAbstractClassName(TableBlueprint $tableBlueprint): string
    {
        return $this->dataAccessGeneratorConfiguration->getParentClassPrefix() . $this->getClassName($tableBlueprint);
    }

    private function getAbstractClassNamespace(TableBlueprint $tableBlueprint): string
    {
        return $this->getClassNamespace($tableBlueprint) . '\\Generated';
    }

    /**
     * @param ClassDefinition $classDefinition
     * @param ClassDefinition $abstractClassDefinition
     */
    private function writeClassFiles(ClassDefinition $classDefinition,
        ClassDefinition $abstractClassDefinition,): void
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
        $abstractDtoFilePath = $abstractDtoClassFolder
            . DIRECTORY_SEPARATOR
            . $abstractClassDefinition->getName()
            . '.php';

        if (!\file_exists($dtoFilePath)) {
            \file_put_contents($dtoFilePath, $dtoClassPhpCode);
        }
        \file_put_contents($abstractDtoFilePath, $abstractDtoPhpCode);
    }

    private function getMapToFailedConstantName(ObjectTypeDefinition $modelObjectTypeDefinition)
    {
        return sprintf('%s_MAP_TO_DTO_FAILED',
            Str::upper($modelObjectTypeDefinition->getImportableName()));
    }

    private function getMapFromFailedConstantName(ObjectTypeDefinition $modelObjectTypeDefinition)
    {
        return sprintf('%s_MAP_FROM_DTO_FAILED',
            Str::upper($modelObjectTypeDefinition->getImportableName()));
    }

    private function getDtoFunctionParameterDefinition(ObjectTypeDefinition $dtoTypeObjectTypeDefinition): FunctionParameterDefinition
    {
        return new FunctionParameterDefinition($this->getDtoVariableName($dtoTypeObjectTypeDefinition),
            PhpTypeEnum::objectOfType($dtoTypeObjectTypeDefinition->getFullyQualifiedName()));
    }

    private function getDtoVariableName(ObjectTypeDefinition $dtoTypeObjectTypeDefinition): string
    {
        $name = $dtoTypeObjectTypeDefinition->getImportableName();
        return strtolower($name[0]) . substr($name, 1);
    }

    private function getUniqueKeyViolationValidationMessageMethod(ClassDefinition $classDefinition,
        IndexBlueprint $indexBlueprint): ClassMethodDefinition
    {
        $returnType = new ObjectTypeDefinition(\Symfony\Component\Translation\TranslatableMessage::class);
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
     * @param TableBlueprint              $tableBlueprint
     * @param ObjectTypeDefinition        $dtoTypeObjectTypeDefinition
     * @param string                      $modelVariableName
     * @param FunctionParameterDefinition $dtoParameterDefinition
     * @param string                      $mapToFailedConstantName
     * @param ObjectTypeDefinition        $returnObjectTypeDefinition
     *
     * @return StatementBlockDefinition
     */
    private function getMapToDtoStatementBlock(TableBlueprint $tableBlueprint,
        ObjectTypeDefinition $dtoTypeObjectTypeDefinition,
        string $modelVariableName,
        FunctionParameterDefinition $dtoParameterDefinition,
        string $mapToFailedConstantName,
        ObjectTypeDefinition $returnObjectTypeDefinition): StatementBlockDefinition
    {
        return (new StatementBlockDefinition(new RawStatementDefinition(sprintf(//    "if (!$this->accountMap->toAccountDto($accountModel, $accountDto))"
            "if (!\$this->%s()->to%s(\$%s, \$%s))",
            $this->modelDataMapGenerator->getModelMapAccessorTraitMethodName($tableBlueprint),
            $dtoTypeObjectTypeDefinition->getImportableName(),
            $modelVariableName,
            $dtoParameterDefinition->getParameterName()))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new LogMessage(self::%s)]);",
            $returnObjectTypeDefinition->getImportableName(),
            $mapToFailedConstantName)));
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
     * @param TableBlueprint              $tableBlueprint
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
    private function getMapFromDtoStatementBlock(TableBlueprint $tableBlueprint,
        ObjectTypeDefinition $dtoTypeObjectTypeDefinition,
        string $modelVariableName,
        FunctionParameterDefinition $dtoParameterDefinition,
        ObjectTypeDefinition $returnTypeObjectTypeDefinition,
        ObjectTypeDefinition $logMessageObjectTypeDefinition,
        ObjectTypeDefinition $modelTypeObjectTypeDefinition,
        string $methodFailedConstantName): StatementBlockDefinition
    {
        return (new StatementBlockDefinition(new RawStatementDefinition(sprintf("if (!\$this->%s()->from%s(\$%s, \$%s))",
            $this->modelDataMapGenerator->getModelMapAccessorTraitMethodName($tableBlueprint),
            $dtoTypeObjectTypeDefinition->getImportableName(),
            $modelVariableName,
            $dtoParameterDefinition->getParameterName()))))->addStatementDefinition(new RawStatementDefinition(sprintf("return %s::error([new %s(static::%s), new %s(static::%s)]);",
            $returnTypeObjectTypeDefinition->getImportableName(),
            $logMessageObjectTypeDefinition->getImportableName(),
            $this->getMapFromFailedConstantName($modelTypeObjectTypeDefinition),
            $logMessageObjectTypeDefinition->getImportableName(),
            $methodFailedConstantName)));
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
}
