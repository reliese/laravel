<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProvider;

interface StatementDefinitionInterface
{
    /**
     * Factors in formatting for code indentation
     *
     * @param IndentationProvider $indentationProvider
     *
     * @return string
     */
    public function toPhpCode(IndentationProvider $indentationProvider): string;
}
