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
        $name = Str::studly(Str::singular($identifier));

        return $prefix . $name . $suffix;
    }
}
