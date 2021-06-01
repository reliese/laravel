<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProvider;
use function implode;
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

    public function toPhpCode(IndentationProvider $indentationProvider): string
    {
        if (empty($this->text)) {
            return "";
        }

        $statements[] = $indentationProvider->getIndentation()."/**";
        foreach ($this->text as $line) {
            $statements[] = $indentationProvider->getIndentation().' * '.$line;
        }
        $statements[] = $indentationProvider->getIndentation()." */";
        return \implode("\n", $statements);
    }
}