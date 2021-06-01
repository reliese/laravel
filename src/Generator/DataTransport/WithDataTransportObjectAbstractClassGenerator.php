<?php

namespace Reliese\Generator\DataTransport;

/**
 * Trait WithDataTransportObjectAbstractClassGenerator
 */
trait WithDataTransportObjectAbstractClassGenerator
{
    /**
     * @var DataTransportObjectAbstractClassGenerator|null
     */
    private ?DataTransportObjectAbstractClassGenerator $dataTransportObjectAbstractClassGenerator = null;

    /**
     * @return DataTransportObjectAbstractClassGenerator
     */
    protected function getDataTransportObjectAbstractClassGenerator(): DataTransportObjectAbstractClassGenerator
    {
        return $this->dataTransportObjectAbstractClassGenerator
            ??= app()->make(DataTransportObjectAbstractClassGenerator::class);
    }
}