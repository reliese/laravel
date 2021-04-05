<?php

namespace Reliese\MetaCode\Tool;

use Illuminate\Support\Str;
/**
 * Class ClassNameTool
 */
abstract class ClassNameTool
{
    private function __construct()
    {
    }

    public static function snakeCaseToClassName(?string $prefix, string $identifier, ?string $suffix):string
    {
        $name = Str::camel(Str::singular($identifier));

        $name[0] = Str::upper($name[0]);

        return $prefix . $name . $suffix;
    }
}
