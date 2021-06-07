<?php

namespace Reliese\Generator\Validator;

/**
 * Trait WithDtoValidatorClassAccessorGenerator
 */
trait WithDtoValidatorClassAccessorGenerator
{
    /**
     * @var DtoValidatorClassAccessorGenerator|null
     */
    private ?DtoValidatorClassAccessorGenerator $modelDataMapClassAccessorGenerator = null;

    /**
     * @return DtoValidatorClassAccessorGenerator
     */
    protected function getDtoValidatorClassAccessorGenerator(): DtoValidatorClassAccessorGenerator
    {
        return $this->modelDataMapClassAccessorGenerator ??= app(DtoValidatorClassAccessorGenerator::class);
    }
}