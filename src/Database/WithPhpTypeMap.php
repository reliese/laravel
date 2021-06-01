<?php

namespace Reliese\Database;

/**
 * Trait WithPhpTypeMap
 */
trait WithPhpTypeMap
{
    private ?PhpTypeMappingInterface $phpTypeMapping = null;

    public function getPhpTypeMapping(): PhpTypeMappingInterface
    {
        return $this->phpTypeMapping ??= app()->make(PhpTypeMappingInterface::class);
    }
}