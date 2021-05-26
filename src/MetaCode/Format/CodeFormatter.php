<?php

namespace Reliese\MetaCode\Format;

use Reliese\Configuration\RelieseConfiguration;
/**
 * Class CodeFormatter
 */
class CodeFormatter
{
    /**
     * @var ClassFormatter
     */
    private ?ClassFormatter $classFormatter = null;

    /**
     * @var IndentionProvider
     */
    private IndentionProvider $indentationProvider;

    public function __construct(RelieseConfiguration $relieseConfiguration)
    {
        $this->indentationProvider = new IndentionProvider($relieseConfiguration);
    }

    /**
     * @return ClassFormatter
     */
    public function getClassFormatter(): ClassFormatter
    {
        return $this->classFormatter ??= new ClassFormatter($this->indentationProvider);
    }
}