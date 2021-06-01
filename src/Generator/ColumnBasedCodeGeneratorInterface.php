<?php

namespace Reliese\Generator;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\PhpFileDefinition;

/**
 * Interface ColumnBasedCodeGenerator
 */
interface ColumnBasedCodeGeneratorInterface
{
    public function getPhpFileDefinition(ColumnOwnerInterface $columnOwner): PhpFileDefinition;
}