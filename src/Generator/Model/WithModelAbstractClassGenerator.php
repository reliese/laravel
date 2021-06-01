<?php

namespace Reliese\Generator\Model;

/**
 * Trait WithModelAbstractClassGenerator
 */
trait WithModelAbstractClassGenerator
{
    /**
     * @var ModelAbstractClassGenerator|null
     */
    private ?ModelAbstractClassGenerator $modelAbstractClassGenerator = null;

    protected function getModelAbstractClassGenerator(): ModelAbstractClassGenerator
    {
        return $this->modelAbstractClassGenerator
            ??= app(ModelAbstractClassGenerator::class);
    }
}