<?php

namespace Reliese\Generator\Validator;

trait WithDtoValidatorClassGenerator
{
    private ?DtoValidatorClassGenerator $dtoValidatorClassGenerator = null;

    protected function getDtoValidatorClassGenerator(): DtoValidatorClassGenerator
    {
        return $this->dtoValidatorClassGenerator ??= app(DtoValidatorClassGenerator::class);
    }
}