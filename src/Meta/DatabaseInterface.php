<?php

namespace Reliese\Meta;

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
}