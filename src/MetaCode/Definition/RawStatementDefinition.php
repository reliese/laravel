<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProviderInterface;
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

    public function toPhpCode(IndentationProviderInterface $indentationProvider, int $blockDepth): string
    {
        return $indentationProvider->getIndentation($blockDepth).$this->rawPhpCode;
    }
}
