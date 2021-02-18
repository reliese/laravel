<?php

/**
 * Created by Cristian.
 * Date: 05/09/16 11:41 PM.
 */

namespace Reliese\Coders\Model\Relations;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Reliese\Coders\Model\Model;
use Reliese\Coders\Model\Relation;
use Reliese\Support\Dumper;

class BelongsTo implements Relation
{
    /**
     * @var \Illuminate\Support\Fluent
     */
    protected $command;

    /**
     * @var \Reliese\Coders\Model\Model
     */
    protected $parent;

    /**
     * @var \Reliese\Coders\Model\Model
     */
    protected $related;

    /**
     * BelongsToWriter constructor.
     *
     * @param \Illuminate\Support\Fluent $command
     * @param \Reliese\Coders\Model\Model $parent
     * @param \Reliese\Coders\Model\Model $related
     */
    public function __construct(Fluent $command, Model $parent, Model $related)
    {
        $this->command = $command;
        $this->parent = $parent;
        $this->related = $related;
    }

    /**
     * @return string
     */
    public function name()
    {
        $relationName = $this->foreignKey();
        $primaryKey = $this->otherKey();
        // chop off 'id'
        if ($this->parent->usesSnakeAttributes()) {
            $lowerPrimaryKey = strtolower($primaryKey);
            $relationName = preg_replace('/(_' . $primaryKey . ')|(_' . $lowerPrimaryKey . ')$/', '', $relationName);
        } else {
            $studlyPrimaryKey = Str::studly($primaryKey);
            $relationName = preg_replace('/(' . $primaryKey . ')|(' . $studlyPrimaryKey . ')$/', '', $relationName);
        }


//        switch ($this->parent->getRelationNameStrategy()) {
//            case 'foreign_key':
//                $relationName = preg_replace("/[^a-zA-Z0-9]?{$this->otherKey()}$/", '', $this->foreignKey());
//                break;
//            default:
//            case 'related':
//                $relationName = $this->related->getClassName();
//                break;
//        }

        if ($this->parent->usesSnakeAttributes()) {
            return Str::snake($relationName);
        }

        return Str::camel($relationName);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function foreignKey($index = 0)
    {
        return $this->command->columns[$index];
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function otherKey($index = 0)
    {
        return $this->command->references[$index];
    }

    /**
     * @return string
     */
    public function body()
    {
        $body = 'return $this->belongsTo(';

        $body .= $this->related->getQualifiedUserClassName() . '::class';

        if ($this->needsForeignKey()) {
            $foreignKey = $this->parent->usesPropertyConstants()
                ? $this->parent->getQualifiedUserClassName() . '::' . strtoupper($this->foreignKey())
                : $this->foreignKey();
            $body .= ', ' . Dumper::export($foreignKey);
        }

        if ($this->needsOtherKey()) {
            $otherKey = $this->related->usesPropertyConstants()
                ? $this->related->getQualifiedUserClassName() . '::' . strtoupper($this->otherKey())
                : $this->otherKey();
            $body .= ', ' . Dumper::export($otherKey);
        }

        $body .= ')';

        if ($this->hasCompositeOtherKey()) {
            // We will assume that when this happens the referenced columns are a composite primary key
            // or a composite unique key. Otherwise it should be a has-many relationship which is not
            // supported at the moment. @todo: Improve relationship resolution.
            foreach ($this->command->references as $index => $column) {
                $body .= "\n\t\t\t\t\t->where(" .
                         Dumper::export($this->qualifiedOtherKey($index)) .
                         ", '=', " .
                         Dumper::export($this->qualifiedForeignKey($index)) .
                         ')';
            }
        }

        $body .= ';';

        return $body;
    }

    /**
     * @return bool
     */
    protected function needsForeignKey()
    {
        $defaultForeignKey = $this->related->getRecordName() . '_id';

        return $defaultForeignKey != $this->foreignKey() || $this->needsOtherKey();
    }

    /**
     * @return bool
     */
    protected function needsOtherKey()
    {
        $defaultOtherKey = $this->related->getPrimaryKey();

        return $defaultOtherKey != $this->otherKey();
    }

    /**
     * Whether the "other key" is a composite foreign key.
     *
     * @return bool
     */
    protected function hasCompositeOtherKey()
    {
        return count($this->command->references) > 1;
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function qualifiedOtherKey($index = 0)
    {
        return $this->related->getTable() . '.' . $this->otherKey($index);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function qualifiedForeignKey($index = 0)
    {
        return $this->parent->getTable() . '.' . $this->foreignKey($index);
    }

    /**
     * @return string
     */
    public function hint()
    {
        $base = $this->related->getQualifiedUserClassName();

        if ($this->isNullable()) {
            $base .= '|null';
        }

        return $base;
    }

    /**
     * @return bool
     */
    private function isNullable()
    {
        return (bool)$this->parent->getBlueprint()->column($this->foreignKey())->get('nullable');
    }
}
