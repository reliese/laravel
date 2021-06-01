<?php

namespace Reliese\Generator\DataTransport;

/**
 * Trait WithDataTransportObjectClassGenerator
 */
trait WithDataTransportObjectClassGenerator
{
    /**
     * @var DataTransportObjectClassGenerator|null
     */
    private ?DataTransportObjectClassGenerator $dataTransportObjectClassGenerator = null;

    /**
     * @return DataTransportObjectClassGenerator
     */
    protected function getDataTransportObjectClassGenerator(): DataTransportObjectClassGenerator
    {
        return $this->dataTransportObjectClassGenerator ??= app()->make(DataTransportObjectClassGenerator::class);
    }
}