<?php

namespace Reliese\Meta;

use Reliese\Meta\MySql\Schema;

/**
 * Interface DatabaseInterface
 */
interface DatabaseInterface
{
    /**
     * Returns an array of accessible schema names
     *
     * @return string[]
     */
    public function getSchemaNames();

    /**
     * @param string $schemaName
     * @return Schema
     */
    public function getSchema($schemaName);
}