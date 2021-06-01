<?php

namespace Reliese\Generator\DataMap;

/**
 * Trait WithModelDataMapClassGenerator
 */
trait WithModelDataMapClassGenerator
{
    /**
     * @var ModelDataMapClassGenerator|null
     */
    private ?ModelDataMapClassGenerator $modelDataMapClassGenerator = null;

    /**
     * @return ModelDataMapClassGenerator
     */
    protected function getModelDataMapClassGenerator(): ModelDataMapClassGenerator
    {
        return $this->modelDataMapClassGenerator ??= app(ModelDataMapClassGenerator::class);
    }
}