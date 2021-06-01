<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Enum\AbstractEnum;
use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Format\IndentationProvider;
use function implode;

/**
 * Class ClassMethodDefinition
 */
class ClassMethodDefinition extends FunctionDefinition implements StatementDefinitionInterface
{
    /**
     * @var AbstractEnum|null
     */
    private ?AbstractEnum $abstractEnum;

    /**
     * @var InstanceEnum|null
     */
    private ?InstanceEnum $instanceEnum;

    /**
     * ClassMethodDefinition constructor.
     *
     * @param string $functionName
     * @param PhpTypeEnum $returnType
     * @param FunctionParameterDefinition[] $functionParameterDefinitions
     * @param VisibilityEnum|null $visibilityEnum
     * @param InstanceEnum|null $instanceEnum
     * @param AbstractEnum|null $abstractEnum
     */
    public function __construct(
        string $functionName,
        PhpTypeEnum $returnType,
        array $functionParameterDefinitions = [],
        ?VisibilityEnum $visibilityEnum = null,
        ?InstanceEnum $instanceEnum = null,
        ?AbstractEnum $abstractEnum = null
    ) {
        parent::__construct($functionName, $returnType, $functionParameterDefinitions, $visibilityEnum);
        $this->instanceEnum = $instanceEnum ?? InstanceEnum::instanceEnum();
        $this->abstractEnum = $abstractEnum ?? AbstractEnum::concreteEnum();
    }

    /**
     * @return AbstractEnum
     */
    public function getAbstractEnum(): AbstractEnum
    {
        return $this->abstractEnum;
    }

    /**
     * @return InstanceEnum
     */
    public function getInstanceEnum(): InstanceEnum
    {
        return $this->instanceEnum;
    }

    public function toPhpCode(IndentationProvider $indentationProvider): string
    {
        $signature = $indentationProvider->getIndentation();

        if ($this->getVisibilityEnum()) {
            $signature .= $this->getVisibilityEnum()->toReservedWord() . ' ';
        }

        if ($this->getAbstractEnum()->isAbstract()) {
            $signature .= $this->getAbstractEnum()->toReservedWord() . ' ';
        }

        $signature .= 'function ' . $this->getFunctionName() . '(';

        $parameters = [];
        /** @var FunctionParameterDefinition $parameter */
        foreach ($this->getFunctionParameterDefinitions() as $parameter) {
            $hint = $parameter->getParameterType()->toTypeHint();

            $parameterPhpCode = $hint . ' ';
            if ($parameter->isOutputParameter()) {
                $parameterPhpCode .= '&';
            }
            $parameterPhpCode .= '$' . $parameter->getParameterName();
            if ($parameter->hasDefaultValueStatementDefinition()) {
                $parameterPhpCode .= ' = '.$parameter->getDefaultValueStatementDefinition()
                        ->toPhpCode(IndentationProvider::NoIndentation());
            }
            $parameters[] = $parameterPhpCode;
        }

        $signature .= implode(', ', $parameters);

        $signature .= ')';
        if ($this->getReturnPhpTypeEnum()->isDefined()) {
            /*
             * This condition is required because constructors do not have return types
             */
            $signature .= ": ". $this->getReturnPhpTypeEnum()->toTypeHint();
        }
        if ($this->getAbstractEnum()->isAbstract()) {
            return $signature . ";\n";
        }
        $signature .= "\n";

        $signature .= $indentationProvider->getIndentation() . "{\n";

        foreach ($this->getBlockStatements() as $statement) {
            $signature .= $statement->toPhpCode($indentationProvider->increment())
                . "\n";
        }


        $signature .= $indentationProvider->getIndentation() . '}';

        return $signature;
    }
}
