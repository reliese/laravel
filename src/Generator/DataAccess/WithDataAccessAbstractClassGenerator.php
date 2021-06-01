<?php

namespace Reliese\Generator\DataAccess;

/**
 * Trait WithDataAccessAbstractClassGenerator
 *
 * @package Reliese\Generator\DataAccess
 */
trait WithDataAccessAbstractClassGenerator
{
    /**
     * @var DataAccessAbstractClassGenerator|null
     */
    private ?DataAccessAbstractClassGenerator $dataAccessAbstractClassGenerator = null;

    /**
     * @return DataAccessAbstractClassGenerator
     */
    protected function getDataAccessAbstractClassGenerator(): DataAccessAbstractClassGenerator
    {
        return $this->dataAccessAbstractClassGenerator ??= app(DataAccessAbstractClassGenerator::class);
    }
}