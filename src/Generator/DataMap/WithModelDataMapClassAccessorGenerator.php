<?php

namespace Reliese\Generator\DataMap;

/**
 * Trait WithModelDataMapClassAccessorGenerator
 */
trait WithModelDataMapClassAccessorGenerator
{
    /**
     * @var ModelDataMapAccessorGenerator|null
     */
    private ?ModelDataMapAccessorGenerator $modelDataMapClassAccessorGenerator = null;

    /**
     * @return ModelDataMapAccessorGenerator
     */
    protected function getModelDataMapClassAccessorGenerator(): ModelDataMapAccessorGenerator
    {
        return $this->modelDataMapClassAccessorGenerator ??= app(ModelDataMapAccessorGenerator::class);
    }
}