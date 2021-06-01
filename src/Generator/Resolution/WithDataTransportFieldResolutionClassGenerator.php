<?php

namespace Reliese\Generator\Resolution;

/**
 * Trait WithDataTransportFieldResolutionClassGenerator
 *
 * @package Reliese\Generator\Resolution
 */
trait WithDataTransportFieldResolutionClassGenerator
{
    /**
     * @var DataTransportFieldResolutionClassGenerator|null
     */
    private ?DataTransportFieldResolutionClassGenerator $dataTransportFieldResolutionClassGenerator =
        null;

    /**
     * @return DataTransportFieldResolutionClassGenerator
     */
    protected function getDataTransportFieldResolutionClassGenerator(): DataTransportFieldResolutionClassGenerator
    {
        return $this->dataTransportFieldResolutionClassGenerator
            ??= app(DataTransportFieldResolutionClassGenerator::class);
    }
}