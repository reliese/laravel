<?php

namespace Reliese\Configuration;

/**
 * Class CodeFormattingConfiguration
 */
class CodeFormattingConfiguration
{
    public function __construct(array $configuration)
    {
        $this->indentationSymbol = $configuration['IndentationSymbol'];
    }
}