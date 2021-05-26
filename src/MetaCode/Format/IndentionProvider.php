<?php

namespace Reliese\MetaCode\Format;

use Reliese\Configuration\RelieseConfiguration;
use function str_repeat;
/**
 * Class DefaultIndentionProvider
 */
class IndentionProvider implements IndentationProviderInterface
{
    private string $indentationSymbol;

    private int $indentationDepth;

    public function __construct(RelieseConfiguration $relieseConfiguration)
    {
        $this->indentationSymbol = $relieseConfiguration->getCodeFormattingConfiguration()->indentationSymbol;
        $this->indentationDepth = 0;
    }

    /**
     * @param int $depth
     *
     * @return string
     */
    public function getIndentation(int $depth): string
    {
        return str_repeat($this->getIndentationSymbol(), $depth);
    }

    /**
     * @return string
     */
    public function getIndentationSymbol(): string
    {
        return '    ';
    }

    public function increment(): IndentionProvider
    {
        return new IndentionProvider($this->indentationSymbol, $this->indentationDepth + 1);
    }

    public function decrement(): IndentionProvider
    {
        return new IndentionProvider($this->indentationSymbol, $this->indentationDepth - 1);
    }
}