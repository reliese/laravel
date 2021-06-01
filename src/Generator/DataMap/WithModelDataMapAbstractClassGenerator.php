<?php

namespace Reliese\Generator\DataMap;

/**
 * Trait WithModelDataMapAbstractClassGenerator
 */
trait WithModelDataMapAbstractClassGenerator
{
    /**
     * @var ModelDataMapAbstractClassGenerator|null
     */
    private ?ModelDataMapAbstractClassGenerator $modelDataMapAbstractClassGenerator = null;

    /**
     * @return ModelDataMapAbstractClassGenerator
     */
    protected function getModelDataMapAbstractClassGenerator(): ModelDataMapAbstractClassGenerator
    {
        return $this->modelDataMapAbstractClassGenerator ??= app(ModelDataMapAbstractClassGenerator::class);
    }
}