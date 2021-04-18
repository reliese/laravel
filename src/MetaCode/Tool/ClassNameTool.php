<?php

namespace Reliese\MetaCode\Tool;

use Illuminate\Support\Str;
/**
 * Class ClassNameTool
 */
abstract class ClassNameTool
{
    /**
     * @param string|null $prefix
     * @param string $identifier
     * @param string|null $suffix
     *
     * @return string
     */
    public static function snakeCaseToClassName(?string $prefix, string $identifier, ?string $suffix):string
    {
        $name = Str::studly(Str::singular($identifier));

        return $prefix . $name . $suffix;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    public static function classNameToParameterName(string $className): string
    {
        return \strtolower($className[0]).\substr($className, 1);
    }

    /**
     * @param string $columnName
     *
     * @return string
     */
    public static function columnNameToPropertyName(string $columnName): string
    {
        return Str::camel($columnName);
    }

    /**
     * @param string $columnName
     *
     * @return string
     */
    public static function columnNameToGetterName(string $columnName): string
    {
        return 'get'.str::studly($columnName);
    }

    /**
     * @param string $columnName
     *
     * @return string
     */
    public static function columnNameToSetterName(string $columnName): string
    {
        return 'set'.str::studly($columnName);
    }
}
