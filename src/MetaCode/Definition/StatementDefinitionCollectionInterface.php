<?php

namespace Reliese\MetaCode\Definition;

interface StatementDefinitionCollectionInterface
{
    public function addStatementDefinition(StatementDefinitionInterface $statementDefinition) : static;

    public function hasStatements(): bool;
}