<?php

namespace Reliese\Generator;

use Reliese\Blueprint\TableBlueprint;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
/**
 * Interface ClassGeneratorInterface
 */
interface ClassGeneratorInterface
{
    public function getClassDefinition(): ClassDefinition;

    public function getObjectTypeDefinition(): ObjectTypeDefinition;
}