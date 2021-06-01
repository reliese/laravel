<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProvider;
/**
 * Class StatementDefinitionCollection
 */
class StatementDefinitionCollection implements StatementDefinitionInterface, StatementDefinitionCollectionInterface
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
    public function toPhpCode(IndentationProvider $indentationProvider): string
    {
        $statements = [];
        foreach ($this->statementDefinitions as $statementDefinition) {
            $statements[] = $statementDefinition->toPhpCode($indentationProvider);
        }
        return \implode("\n", $statements);
    }

    public function hasStatements(): bool
    {
        return !empty($this->statementDefinitions);
    }
}
