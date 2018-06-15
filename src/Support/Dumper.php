<?php

/**
 * Created by Cristian.
 * Date: 11/09/16 05:18 PM.
 */

namespace Reliese\Support;

class Dumper
{
    /**
     * Analyzed passed value and returns true if passed value uses class static call.
     *
     * @param mixed $value Value to check
     *
     * @return bool
     */
    private static function hasStaticCall($value)
    {
        return is_string($value) && strpos($value, '::') !== false;
    }

    /**
     * @param mixed $value
     * @param int $tabs
     *
     * @return string
     */
    public static function export($value, $tabs = 2)
    {
        // Custom array exporting
        if (is_array($value)) {
            $indent = str_repeat("\t", $tabs);
            $closingIndent = str_repeat("\t", $tabs - 1);
            $keys = array_keys($value);
            $array = array_map(function ($value, $key) use ($tabs) {
                if (is_numeric($key)) {
                    return static::export($value, $tabs + 1);
                }

                $key = static::hasStaticCall($key) ? $key : "'$key'";

                return "$key => ".static::export($value, $tabs + 1);
            }, $value, $keys);

            return "[\n$indent".implode(",\n$indent", $array)."\n$closingIndent]";
        }

        // Default variable exporting
        return static::hasStaticCall($value) ? $value : var_export($value, true);
    }
}
