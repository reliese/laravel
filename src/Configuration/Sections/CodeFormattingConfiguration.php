<?php

namespace Reliese\Configuration\Sections;

/**
 * Class CodeFormattingConfiguration
 */
class CodeFormattingConfiguration
{
    const INDENTATION_SYMBOL = 'IndentationSymbol';

    private string $indentationSymbol;

    public function __construct(array $configuration)
    {
        $this->setIndentationSymbol($configuration[self::INDENTATION_SYMBOL]);
    }

    /**
     * @return mixed
     */
    public function getIndentationSymbol(): mixed
    {
        return $this->indentationSymbol;
    }

    /**
     * @param mixed $indentationSymbol
     *
     * @return CodeFormattingConfiguration
     */
    public function setIndentationSymbol(mixed $indentationSymbol): CodeFormattingConfiguration
    {
        $this->indentationSymbol = $indentationSymbol;
        return $this;
    }

    public function toArray(): array
    {
        return [
            self::INDENTATION_SYMBOL => $this->getIndentationSymbol(),
        ];
    }
}