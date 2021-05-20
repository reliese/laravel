<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProviderInterface;
/**
 * Class CommentBlockStatementDefinition
 */
class CommentBlockStatementDefinition implements StatementDefinitionInterface
{
    /**
     * @var string[]
     */
    private array $text = [];

    public function addLine(string $line):static
    {
        $this->text[] = $line;
        return $this;
    }

    public function toPhpCode(IndentationProviderInterface $indentationProvider, int $blockDepth): string
    {
        if (empty($this->text)) {
            return "";
        }

        $statements[] = $indentationProvider->getIndentation($blockDepth)."/**";
        foreach ($this->text as $line) {
            $statements[] = $indentationProvider->getIndentation($blockDepth).' * '.$line;
        }
        $statements[] = $indentationProvider->getIndentation($blockDepth)." */";
        return \implode("\n", $statements);
    }
}