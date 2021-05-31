<?php

namespace Reliese\MetaCode\Format;

use Reliese\Configuration\ConfigurationProfile;

/**
 * Class IndentionProvider
 */
class IndentationProvider
{
    private string $indentationSymbol;

    private int $indentationDepth;

    protected function __construct($indentationSymbol, $indentationDepth)
    {
        $this->indentationSymbol = $indentationSymbol;
        $this->indentationDepth = $indentationDepth;
    }

    public static function fromConfig(
        ConfigurationProfile $configurationProfile
    ) {
        return new static($configurationProfile->getCodeFormattingConfiguration()->getIndentationSymbol(), 0);
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

    public function increment(): IndentationProvider
    {
        return new IndentationProvider($this->getIndentationSymbol(), $this->getIndentationDepth() + 1);
    }

    public function decrement(): IndentationProvider
    {
        return new IndentationProvider($this->getIndentationSymbol(), $this->getIndentationDepth() - 1);
    }

    /**
     * @return int
     */
    public function getIndentationDepth(): int
    {
        return $this->indentationDepth;
    }
}