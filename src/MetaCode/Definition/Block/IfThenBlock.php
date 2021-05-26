<?php

namespace Reliese\MetaCode\Definition\Block;

use Illuminate\Support\Str;
use Reliese\MetaCode\Definition\StatementBlockDefinition;
use Reliese\MetaCode\Definition\StatementDefinitionInterface;
use Reliese\MetaCode\Format\IndentationProviderInterface;
use function ltrim;
use function trim;
/**
 * Class IfBlock
 */
class IfThenBlock implements StatementDefinitionInterface
{
    /**
     * @var StatementDefinitionInterface
     */
    private StatementDefinitionInterface $conditionStatement;

    /**
     * @var StatementDefinitionInterface
     */
    private StatementDefinitionInterface $thenStatements;

    public function __construct(
        StatementDefinitionInterface $conditionStatement,
        StatementDefinitionInterface $thenStatements,
    ) {
        $this->conditionStatement = $conditionStatement;
        $this->thenStatements = $thenStatements;
    }

    /**
     * @return string
     */
    public function toPhpCode(IndentationProviderInterface $indentationProvider, int $blockDepth): string
    {
        $ifStatement = "";
        $suffixStatement = "";


        $ifStatement = sprintf(
            "if (%s) ",
            // remove whitespaces
            trim($this->blockPrefixStatement->toPhpExpression($indentationProvider, $blockDepth))
        );


        if ($this->blockSuffixStatement instanceof StatementBlockDefinition) {
            $suffixStatement = " ".ltrim($this->blockSuffixStatement->toPhpCode($indentationProvider, $blockDepth));
        }

        return \sprintf(
            "%s{\n%s\n%s}%s\n",
            $ifStatement,
            $this->statementDefinitionCollection->toPhpCode($indentationProvider, $blockDepth + 1),
            $indentationProvider->getIndentation($blockDepth),
            $suffixStatement
        );
    }
}