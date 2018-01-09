<?php

/**
 * Created by Cristian.
 * Date: 11/09/16 09:26 PM.
 */

namespace Reliese\Coders\Model\Relations;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

class HasMany extends HasOneOrMany
{
    /**
     * @return string
     */
    public function hint()
    {
        return '\\'.Collection::class;
    }

    /**
     * @return string
     */
    public function name()
    {
        $relationBaseName = Str::plural(Str::singular($this->related->getTable(true)));

        switch ($this->parent->getRelationNameStrategy()) {
            case 'foreign_key':
                $suffix = preg_replace("/[^a-zA-Z0-9]?{$this->localKey()}$/", '', $this->foreignKey());

                $relationName = $relationBaseName;

                // Don't make relations such as users_user, just leave it as 'users'.
                if ($this->parent->getTable(true) !== $suffix) {
                    $relationName .= "_{$suffix}";
                }

                break;
            case 'related':
            default:
                $relationName = $relationBaseName;
                break;
        }

        if ($this->parent->usesSnakeAttributes()) {
            return Str::snake($relationName);
        }

        return Str::camel($relationName);
    }

    /**
     * @return string
     */
    public function method()
    {
        return 'hasMany';
    }
}
