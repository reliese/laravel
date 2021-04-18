<?php

namespace Reliese\Blueprint;

/**
 * Interface SchemaMemberInterface
 *
 * Defines methods that are common to all objects that belong to a schema
 *
 * @package Reliese\Blueprint
 */
interface SchemaMemberInterface
{
    /**
     * Returns the name that uniquely identifies this item withing a Schema
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns an instance of SchemaMemberType
     *
     * @return SchemaMemberType
     */
    public function getSchemaMemberType() : SchemaMemberType;
}