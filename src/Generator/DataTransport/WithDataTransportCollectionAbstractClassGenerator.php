<?php

namespace Reliese\Generator\DataTransport;

/**
 * Trait WithDataTransportCollectionAbstractClassGenerator
 *
 * @package Reliese\Generator\DataTransport
 */
trait WithDataTransportCollectionAbstractClassGenerator
{
    /**
     * @var DataTransportCollectionAbstractClassGenerator|null
     */
    private ?DataTransportCollectionAbstractClassGenerator $dataTransportCollectionAbstractClassGenerator = null;

    /**
     * @return DataTransportCollectionAbstractClassGenerator
     */
    protected function getDataTransportCollectionAbstractClassGenerator(): DataTransportCollectionAbstractClassGenerator
    {
        return $this->dataTransportCollectionAbstractClassGenerator
            ??= app(DataTransportCollectionAbstractClassGenerator::class);
    }
}