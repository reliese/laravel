<?php

namespace Reliese;

use const DIRECTORY_SEPARATOR;

/**
 * Class PackagePaths
 */
abstract class PackagePaths
{
    public static function getExampleConfigDirectoryPath():string
    {
        return \realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config');
    }

    public static function getExampleModelConfigFilePath():string
    {
        return static::getExampleConfigDirectoryPath().DIRECTORY_SEPARATOR.'models.php';
    }

    public static function getExampleConfigFilePath():string
    {
        return static::getExampleConfigDirectoryPath().DIRECTORY_SEPARATOR.'reliese.php';
    }
}
