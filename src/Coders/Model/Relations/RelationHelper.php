<?php

namespace Reliese\Coders\Model\Relations;

use Illuminate\Support\Str;

/**
 * General utility functions for dealing with relationships
 */
class RelationHelper
{
    /**
     * Turns a column name like 'manager_id' into 'manager'; or 'lineManagerId' into 'lineManager'.
     *
     * @param bool $usesSnakeAttributes
     * @param string $primaryKey
     * @param string $foreignKey
     * @return string
     */
    public static function stripSuffixFromForeignKey($usesSnakeAttributes, $primaryKey, $foreignKey)
    {
        if ($usesSnakeAttributes) {
            $lowerPrimaryKey = strtolower($primaryKey);
            return preg_replace('/(_)(' . $primaryKey . '|' . $lowerPrimaryKey . ')$/', '', $foreignKey);
        } else {
            $studlyPrimaryKey = Str::studly($primaryKey);
            return preg_replace('/(' . $primaryKey . '|' . $studlyPrimaryKey . ')$/', '', $foreignKey);
        }
    }
}
