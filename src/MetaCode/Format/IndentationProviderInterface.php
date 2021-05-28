<?php

namespace Reliese\MetaCode\Format;

/**
 * Interface IndentationProviderInterface
 */
interface IndentationProviderInterface
{
    /**
     * @param int $depth
     *
     * @return string
     */
    public function getIndentation(int $depth): string;

    /**
     * @return string
     */
    public function getIndentationSymbol(): string;
}