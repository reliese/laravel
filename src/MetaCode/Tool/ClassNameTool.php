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
    public static function columnNameToConstantName(string $columnName): string
    {
        return Str::upper(Str::snake($columnName));
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

    public static function dtoClassNameToVariableName(string $dtoClassName)
    {
        return \strtolower($dtoClassName[0]).\substr($dtoClassName, 1);
    }

    public static function variableNameToGetterName(string $variableName)
    {
        return 'get'.str::studly($variableName);
    }

    public static function variableNameToSetterName(string $variableName)
    {
        return 'set'.str::studly($variableName);
    }

    public static function fullyQualifiedClassNameToClassName(string $fullyQualifiedClassName): string
    {
        $parts = explode('\\', \ltrim($fullyQualifiedClassName, '\\'));
        return trim(\array_pop($parts));
    }

    public static function fullyQualifiedClassNameToNamespace(string $fullyQualifiedClassName): string
    {
        $parts = explode('\\', \ltrim($fullyQualifiedClassName, '\\'));
        \array_pop($parts);
        $namespace = \implode('\\', $parts);
        return \implode('\\', $parts);
    }

    public static function globalClassFQN(string $fullyQualifiedTypeName): string
    {
        return "\\".trim($fullyQualifiedTypeName, "\\");
    }

    public static function identifierNameToConstantName(string $identifierName): string
    {
        return \strtoupper(str::snake($identifierName));
    }

    public static function fkNameToVariableName(string $foreignKeyname): string
    {
        $name = str::studly($foreignKeyname);
        return \strtolower($name[0]).\substr($foreignKeyname, 1);
    }

    public static function fullyQualifiedName(string $name)
    {
        return '\\'. ltrim($name, '\\');
    }
}
