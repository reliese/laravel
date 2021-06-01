<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProvider;
/**
 * Class RawStatementDefinition
 */
class RawStatementDefinition implements StatementDefinitionInterface
{
    private string $rawPhpCode;

    public function __construct(string $rawPhpCode)
    {
        $this->rawPhpCode = trim($rawPhpCode);
    }

    public function toPhpCode(IndentationProvider $indentationProvider): string
    {
        return $indentationProvider->getIndentation().$this->rawPhpCode;
    }
}
