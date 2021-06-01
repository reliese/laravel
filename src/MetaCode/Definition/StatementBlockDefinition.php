<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProvider;

/**
 * Class StatementBlockDefinition
 */
class StatementBlockDefinition implements StatementDefinitionInterface, StatementDefinitionCollectionInterface
{
    /**
     * @var StatementDefinitionInterface|null
     */
    private ?StatementDefinitionInterface $blockPrefixStatement;

    /**
     * @var StatementDefinitionCollectionInterface
     */
    private StatementDefinitionCollectionInterface $statementDefinitionCollection;

    /**
     * StatementBlockDefinition constructor.
     *
     * @param StatementDefinitionInterface|null  $blockPrefixStatement
     * @param StatementDefinitionCollectionInterface|null $statementDefinitionCollection
     */
    public function __construct(
        ?StatementDefinitionInterface $blockPrefixStatement,
        ?StatementDefinitionCollectionInterface $statementDefinitionCollection = null,
    ) {
        $this->blockPrefixStatement = $blockPrefixStatement;
        $this->statementDefinitionCollection = $statementDefinitionCollection ?? new StatementDefinitionCollection();
    }

    private ?StatementDefinitionInterface $blockSuffixStatement = null;
    public function addStatementDefinition(StatementDefinitionInterface $statementDefinition) : static
    {
        $this->statementDefinitionCollection->addStatementDefinition($statementDefinition);
        return $this;
    }

    public function hasStatements(): bool
    {
        return $this->statementDefinitionCollection->hasStatements();
    }
    /**
     * @return string
     */
    public function toPhpCode(IndentationProvider $indentationProvider): string
    {
        $prefixStatement = "";
        $suffixStatement = "";

        if ($this->blockPrefixStatement instanceof StatementDefinitionInterface) {
            $prefixStatement = $this->blockPrefixStatement->toPhpCode($indentationProvider)." ";
        }

        if ($this->blockSuffixStatement instanceof StatementBlockDefinition) {
            $suffixStatement = " ".ltrim($this->blockSuffixStatement->toPhpCode($indentationProvider));
        }

        return \sprintf(
            "%s{\n%s\n%s}%s\n",
            $prefixStatement,
            $this->statementDefinitionCollection->toPhpCode($indentationProvider->increment()),
            $indentationProvider->getIndentation(),
            $suffixStatement
        );
    }

    /**
     * @param StatementDefinitionInterface|null $blockSuffixStatement
     *
     * @return StatementBlockDefinition
     */
    public function setBlockSuffixStatement(?StatementDefinitionInterface $blockSuffixStatement): StatementBlockDefinition
    {
        $this->blockSuffixStatement = $blockSuffixStatement;
        return $this;
    }
}
