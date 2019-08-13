<?php

namespace Pursehouse\Modeler\Coders\Model\Relations;

use Illuminate\Support\Str;
use Pursehouse\Modeler\Support\Dumper;
use Illuminate\Support\Fluent;
use Pursehouse\Modeler\Coders\Model\Model;
use Pursehouse\Modeler\Coders\Model\Relation;

class BelongsTo implements Relation
{
    /**
     * @var \Illuminate\Support\Fluent
     */
    protected $command;

    /**
     * @var \Pursehouse\Modeler\Coders\Model\Model
     */
    protected $parent;

    /**
     * @var \Pursehouse\Modeler\Coders\Model\Model
     */
    protected $related;

    /**
     * BelongsToWriter constructor.
     *
     * @param \Illuminate\Support\Fluent  $command
     * @param \Pursehouse\Modeler\Coders\Model\Model $parent
     * @param \Pursehouse\Modeler\Coders\Model\Model $related
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
        switch ($this->parent->getRelationNameStrategy()) {
            case 'foreign_key':
                $relationName = preg_replace("/[^a-zA-Z0-9]?{$this->otherKey()}$/", '', $this->foreignKey());
                break;
            default:
            case 'related':
                $relationName = $this->related->getClassName();
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
    public function body()
    {
        $body = 'return $this->belongsTo(';

        $body .= $this->related->getQualifiedUserClassName().'::class';

        if ($this->needsForeignKey()) {
            $foreignKey = $this->parent->usesPropertyConstants()
                ? $this->parent->getQualifiedUserClassName().'::'.strtoupper($this->foreignKey())
                : $this->foreignKey();
            $body .= ', '.Dumper::export($foreignKey);
        }

        if ($this->needsOtherKey()) {
            $otherKey = $this->related->usesPropertyConstants()
                ? $this->related->getQualifiedUserClassName().'::'.strtoupper($this->otherKey())
                : $this->otherKey();
            $body .= ', '.Dumper::export($otherKey);
        }

        $body .= ')';

        if ($this->hasCompositeOtherKey()) {
            // We will assume that when this happens the referenced columns are a composite primary key
            // or a composite unique key. Otherwise it should be a has-many relationship which is not
            // supported at the moment. @todo: Improve relationship resolution.
            foreach ($this->command->references as $index => $column) {
                $body .= "\n\t\t\t\t\t->where(".
                    Dumper::export($this->qualifiedOtherKey($index)).
                    ", '=', ".
                    Dumper::export($this->qualifiedForeignKey($index)).
                    ')';
            }
        }

        $body .= ';';

        return $body;
    }

    /**
     * @return string
     */
    public function hint()
    {
        return $this->related->getQualifiedUserClassName();
    }

    /**
     * @return bool
     */
    protected function needsForeignKey()
    {
        $defaultForeignKey = $this->related->getRecordName().'_id';

        if ($this->parent->shouldQualifyTableName()) {
            $defaultForeignKey = $this->parent->getTable().'_'.$defaultForeignKey;
        }

        return $defaultForeignKey != $this->foreignKey() || $this->needsOtherKey();
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
    protected function qualifiedForeignKey($index = 0)
    {
        return $this->parent->getTable().'.'.$this->foreignKey($index);
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
     * @param int $index
     *
     * @return string
     */
    protected function otherKey($index = 0)
    {
        return $this->command->references[$index];
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function qualifiedOtherKey($index = 0)
    {
        return $this->related->getTable().'.'.$this->otherKey($index);
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
}
