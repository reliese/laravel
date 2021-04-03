<?php

namespace Reliese\MetaCode\Definition;

/**
 * Class FunctionDefinition
 */
class FunctionDefinition
{
    /**
     * @var string
     */
    private string $functionName;

    /**
     * @var FunctionParameterDefinition[]
     */
    private array $functionParameterDefinitions;

    /**
     * @var PhpTypeEnum
     */
    private PhpTypeEnum $returnPhpTypeEnum;

    /**
     * @var VisibilityEnum|null
     */
    private ?VisibilityEnum $visibilityEnum;

    /**
     * ClassFunctionDefinition constructor.
     *
     * @param string $functionName
     * @param PhpTypeEnum $returnType
     * @param FunctionParameterDefinition[] $functionParameterDefinitions
     * @param VisibilityEnum|null $visibilityEnum
     * @param InstanceEnum|null $instanceEnum
     * @param AbstractEnum|null $abstractEnum
     */
    public function __construct(string $functionName,
        PhpTypeEnum $returnType,
        array $functionParameterDefinitions,
        ?VisibilityEnum $visibilityEnum = null)
    {
        $this->functionName = $functionName;
        $this->returnPhpTypeEnum = $returnType;
        $this->functionParameterDefinitions = $functionParameterDefinitions;
        $this->visibilityEnum = $visibilityEnum ?? VisibilityEnum::publicEnum();
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @return FunctionParameterDefinition[]
     */
    public function getFunctionParameterDefinitions(): array
    {
        return $this->functionParameterDefinitions;
    }

    /**
     * @return PhpTypeEnum
     */
    public function getReturnPhpTypeEnum(): PhpTypeEnum
    {
        return $this->returnPhpTypeEnum;
    }

    /**
     * @return VisibilityEnum
     */
    public function getVisibilityEnum(): VisibilityEnum
    {
        return $this->visibilityEnum;
    }

    /**
     * @param StatementDefinition $statementDefinition
     *
     * @return $this
     */
    public function appendBodyStatement(StatementDefinition $statementDefinition) : static
    {
        $this->blockStatements[] = $statementDefinition;
        return $this;
    }
}
