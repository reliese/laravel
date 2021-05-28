<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Format\IndentationProviderInterface;

interface StatementDefinitionInterface
{
    public function toPhpCode(IndentationProviderInterface $indentationProvider, int $blockDepth): string;
}
