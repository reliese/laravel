<?php

namespace Reliese\Generator\Model;

/**
 * Trait WithModelClassGenerator
 */
trait WithModelClassGenerator
{
    private ?ModelClassGenerator $modelClassGenerator = null;

    protected function getModelClassGenerator(): ModelClassGenerator
    {
        return $this->modelClassGenerator ??= app(ModelClassGenerator::class);
    }
}