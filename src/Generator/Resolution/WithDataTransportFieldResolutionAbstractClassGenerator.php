<?php

namespace Reliese\Generator\Resolution;

/**
 * Trait WithDataTransportFieldResolutionAbstractClassGenerator
 *
 * @package Reliese\Generator\Resolution
 */
trait WithDataTransportFieldResolutionAbstractClassGenerator
{
    /**
     * @var DataTransportFieldResolutionAbstractClassGenerator|null
     */
    private ?DataTransportFieldResolutionAbstractClassGenerator $dataTransportFieldResolutionAbstractClassGenerator =
        null;

    /**
     * @return DataTransportFieldResolutionAbstractClassGenerator
     */
    protected function getDataTransportFieldResolutionAbstractClassGenerator(): DataTransportFieldResolutionAbstractClassGenerator
    {
        return $this->dataTransportFieldResolutionAbstractClassGenerator
            ??= app(DataTransportFieldResolutionAbstractClassGenerator::class);
    }
}