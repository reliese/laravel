<?php

namespace Reliese\Generator\DataAccess;

/**
 * Trait WithDataAccessClassGenerator
 *
 * @package Reliese\Generator\DataAccess
 */
trait WithDataAccessClassGenerator
{
    /**
     * @var DataAccessClassGenerator|null
     */
    private ?DataAccessClassGenerator $dataAccessClassGenerator = null;

    /**
     * @return DataAccessClassGenerator
     */
    protected function getDataAccessClassGenerator(): DataAccessClassGenerator
    {
        return $this->dataAccessClassGenerator ??= app(DataAccessClassGenerator::class);
    }
}