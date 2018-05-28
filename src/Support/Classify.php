<?php

/**
 * Created by Cristian.
 * Date: 05/09/16 11:27 PM.
 */

namespace Reliese\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Classify
{
    /**
     * @param string $name
     * @param string $value
     *
     * @return string
     */
    public function annotation($name, $value)
    {
        return "\n * @$name $value";
    }

    /**
     * Constant template.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return string
     */
    public function constant($name, $value)
    {
        $value = Dumper::export($value);

        return "\tconst $name = $value;\n";
    }

    /**
     * Field template.
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function field($name, $value, $options = [])
    {
        $value = Dumper::export($value);
        $before = Arr::get($options, 'before', '');
        $visibility = Arr::get($options, 'visibility', 'protected');
        $after = Arr::get($options, 'after', "\n");

        return "$before\t$visibility \$$name = $value;$after";
    }

    /**
     * @param string $name
     * @param string $body
     * @param array $options
     *
     * @return string
     */
    public function method($name, $body, $options = [])
    {
        $visibility = Arr::get($options, 'visibility', 'public');

        return "\n\t$visibility function $name()\n\t{\n\t\t$body\n\t}\n";
    }

    public function mixin($class)
    {
        if (Str::startsWith($class, '\\')) {
            $class = Str::replaceFirst('\\', '', $class);
        }

        return "\tuse \\$class;\n";
    }
}
