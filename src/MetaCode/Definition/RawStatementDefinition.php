<?php

namespace Reliese\MetaCode\Definition;

/**
 * Class RawStatementDefinition
 */
class RawStatementDefinition implements StatementDefinitionInterface
{
    private string $rawPhpCode;

    public function __construct(string $rawPhpCode)
    {
        $this->rawPhpCode = $rawPhpCode;
    }

    public function toPhpCode(): string
    {
        return $this->rawPhpCode;
    }
}
