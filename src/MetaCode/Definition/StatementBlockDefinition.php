<?php

namespace Reliese\MetaCode\Definition;

use Illuminate\Support\Str;
use Reliese\MetaCode\Format\IndentationProviderInterface;
/**
 * Class StatementBlockDefinition
 */
class StatementBlockDefinition extends StatementDefinitionCollection
{
    /**
     * @var StatementDefinitionInterface|null
     */
    private ?StatementDefinitionInterface $blockPrefixStatement;

    public function __construct(
        ?StatementDefinitionInterface $blockPrefixStatement
    ) {
        $this->blockPrefixStatement = $blockPrefixStatement;
    }

    private ?StatementDefinitionInterface $blockSuffixStatement = null;

    /**
     * @return string
     */
    public function toPhpCode(IndentationProviderInterface $indentationProvider, int $blockDepth): string
    {
        $prefixStatement = "";
        $suffixStatement = "";

        if ($this->blockPrefixStatement instanceof StatementDefinitionInterface) {
            $prefixStatement = $this->blockPrefixStatement->toPhpCode($indentationProvider, $blockDepth)." ";
        }

        if ($this->blockSuffixStatement instanceof StatementBlockDefinition) {
            $suffixStatement = " ".ltrim($this->blockSuffixStatement->toPhpCode($indentationProvider, $blockDepth));
        }

        return \sprintf(
            "%s{\n%s\n%s}%s\n",
            $prefixStatement,
            parent::toPhpCode($indentationProvider, $blockDepth + 1),
            $indentationProvider->getIndentation($blockDepth),
            $suffixStatement
        );
    }

    /**
     * @param StatementDefinitionInterface|null $blockSuffixStatement
     *
     * @return StatementBlockDefinition
     */
    public function setBlockSuffixStatement(?StatementDefinitionInterface $blockSuffixStatement): StatementBlockDefinition
    {
        $this->blockSuffixStatement = $blockSuffixStatement;
        return $this;
    }
}
