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

    protected function __construct(string $indentationSymbol, int $indentationDepth)
    {
        $this->indentationSymbol = $indentationSymbol;
        $this->indentationDepth = $indentationDepth;
    }

    public static function fromConfig(
        ConfigurationProfile $configurationProfile
    ) {
        return new static($configurationProfile->getCodeFormattingConfiguration()->getIndentationSymbol(), 0);
    }

    public static function NoIndentation(): static
    {
        return new static('', 0);
    }

    /**
     * @param int $indentationDepthOverride
     *
     * @return string
     */
    public function getIndentation(int $indentationDepthOverride = -1): string
    {
        if ($indentationDepthOverride > -1) {
            return str_repeat($this->getIndentationSymbol(), $indentationDepthOverride);
        }

        return str_repeat($this->getIndentationSymbol(), $this->getIndentationDepth());
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