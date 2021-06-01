<?php

namespace Reliese\Generator\Validator;

/**
 * Trait WithDtoValidatorAbstractClassGenerator
 *
 * @package Reliese\Generator\Validator
 */
trait WithDtoValidatorAbstractClassGenerator
{
    /**
     * @var DtoValidatorAbstractClassGenerator|null
     */
    private ?DtoValidatorAbstractClassGenerator $dtoValidatorAbstractClassGenerator = null;

    /**
     * @return DtoValidatorAbstractClassGenerator
     */
    protected function getDtoValidatorAbstractClassGenerator(): DtoValidatorAbstractClassGenerator
    {
        return $this->dtoValidatorAbstractClassGenerator ??= app(DtoValidatorAbstractClassGenerator::class);
    }
}