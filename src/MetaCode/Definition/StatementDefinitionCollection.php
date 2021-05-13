<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProviderInterface;
/**
 * Class StatementDefinitionCollection
 */
class StatementDefinitionCollection implements StatementDefinitionInterface
{
    /**
     * @var StatementDefinitionInterface[]
     */
    private array $statementDefinitions = [];

    /**
     * @param StatementDefinitionInterface $statementDefinition
     *
     * @return $this
     */
    public function addStatementDefinition(StatementDefinitionInterface $statementDefinition) : static
    {
        $this->statementDefinitions[] = $statementDefinition;
        return $this;
    }

    /**
     * @return string
     */
    public function toPhpCode(IndentationProviderInterface $indentationProvider, int $blockDepth): string
    {
        $statements = [];
        foreach ($this->statementDefinitions as $statementDefinition) {
            $statements[] = $statementDefinition->toPhpCode($indentationProvider, $blockDepth);
        }
        return \implode("\n", $statements);
    }
}
