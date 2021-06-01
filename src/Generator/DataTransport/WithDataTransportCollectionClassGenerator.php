<?php

namespace Reliese\Generator\DataTransport;

/**
 * Trait WithDataTransportCollectionClassGenerator
 *
 * @package Reliese\Generator\DataTransport
 */
trait WithDataTransportCollectionClassGenerator
{
    /**
     * @var DataTransportCollectionClassGenerator|null
     */
    private ?DataTransportCollectionClassGenerator $dataTransportCollectionClassGenerator = null;

    /**
     * @return DataTransportCollectionClassGenerator
     */
    protected function getDataTransportCollectionClassGenerator(): DataTransportCollectionClassGenerator
    {
        return $this->dataTransportCollectionClassGenerator ??= app(DataTransportCollectionClassGenerator::class);
    }
}