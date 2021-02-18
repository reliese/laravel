<?php

/**
 * Created by Cristian.
 * Date: 11/09/16 09:26 PM.
 */

namespace Reliese\Coders\Model\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class HasMany extends HasOneOrMany
{
    /**
     * @return string
     */
    public function hint()
    {
        return '\\'.Collection::class.'|'.$this->related->getQualifiedUserClassName().'[]';
    }

    /**
     * @return string
     */
    public function name()
    {
        $relationName = $this->foreignKey();
        $primaryKey = $this->localKey();
        // Chop off primary key suffix of foreign key, if it exists (eg. lineManagerId => lineManager)
        if ($this->parent->usesSnakeAttributes()) {
            $lowerPrimaryKey = strtolower($primaryKey);
            $relationName = preg_replace('/(_' . $primaryKey . ')|(_' . $lowerPrimaryKey . ')$/', '', $relationName);
        } else {
            $studlyPrimaryKey = Str::studly($primaryKey);
            $relationName = preg_replace('/(' . $primaryKey . ')|(' . $studlyPrimaryKey . ')$/', '', $relationName);
        }

        if (strtolower($relationName) === strtolower($this->parent->getClassName())) {
            $relationName = Str::plural($this->related->getClassName());
        } else {
            $relationName = Str::plural($this->related->getClassName()) . 'Where' . ucfirst(Str::singular($relationName));
        }

//        if ($this->parent->shouldPluralizeTableName()) {
//            $relationBaseName = Str::plural(Str::singular($this->related->getTable(true)));
//        } else {
//            $relationBaseName = $this->related->getTable(true);
//        }
//
//        if ($this->parent->shouldLowerCaseTableName()) {
//            $relationBaseName = strtolower($relationBaseName);
//        }
//
//        switch ($this->parent->getRelationNameStrategy()) {
//            case 'foreign_key':
//                $suffix = preg_replace("/[^a-zA-Z0-9]?{$this->localKey()}$/", '', $this->foreignKey());
//
//                $relationName = $relationBaseName;
//
//                // Don't make relations such as users_user, just leave it as 'users'.
//                if ($this->parent->getTable(true) !== $suffix) {
//                    $relationName .= "_{$suffix}";
//                }
//
//                break;
//            case 'related':
//            default:
//                $relationName = $relationBaseName;
//                break;
//        }

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
