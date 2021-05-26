<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProviderInterface;

interface StatementDefinitionInterface
{
    /**
     * Factors in formatting for code indentation
     *
     * @param IndentationProviderInterface $indentationProvider
     * @param int                          $blockDepth
     *
     * @return string
     */
    public function toPhpCode(IndentationProviderInterface $indentationProvider, int $blockDepth): string;
}
