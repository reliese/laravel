<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;

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
     * @var StatementDefinitionInterface[]
     */
    private array $blockStatements = [];

    /**
     * ClassFunctionDefinition constructor.
     *
     * @param string $functionName
     * @param PhpTypeEnum $returnType
     * @param FunctionParameterDefinition[] $functionParameterDefinitions
     * @param VisibilityEnum|null $visibilityEnum
     */
    public function __construct(
        string $functionName,
        PhpTypeEnum $returnType,
        array $functionParameterDefinitions,
        ?VisibilityEnum $visibilityEnum = null
    )
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
     * @return bool
     */
    public function hasFunctionParameterDefinitions(): bool
    {
        return !empty($this->functionParameterDefinitions);
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
     * @param StatementDefinitionInterface $statementDefinition
     *
     * @return $this
     */
    public function appendBodyStatement(StatementDefinitionInterface $statementDefinition): FunctionDefinition
    {
        $this->blockStatements[] = $statementDefinition;
        return $this;
    }

    /**
     * @return StatementDefinitionInterface[]
     */
    public function getBlockStatements(): array
    {
        return $this->blockStatements;
    }
}
