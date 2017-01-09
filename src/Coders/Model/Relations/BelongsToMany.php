<?php

/**
 * Created by Cristian.
 * Date: 05/10/16 11:47 PM.
 */

namespace Reliese\Coders\Model\Relations;

use Illuminate\Support\Str;
use Reliese\Support\Dumper;
use Illuminate\Support\Fluent;
use Reliese\Coders\Model\Model;
use Reliese\Coders\Model\Relation;
use Illuminate\Database\Eloquent\Collection;

class BelongsToMany implements Relation
{
    /**
     * @var \Illuminate\Support\Fluent
     */
    protected $parentCommand;

    /**
     * @var \Illuminate\Support\Fluent
     */
    protected $referenceCommand;

    /**
     * @var \Reliese\Coders\Model\Model
     */
    protected $parent;

    /**
     * @var \Reliese\Coders\Model\Model
     */
    protected $pivot;

    /**
     * @var \Reliese\Coders\Model\Model
     */
    protected $reference;

    /**
     * BelongsToMany constructor.
     *
     * @param \Illuminate\Support\Fluent $parentCommand
     * @param \Illuminate\Support\Fluent $referenceCommand
     * @param \Reliese\Coders\Model\Model $parent
     * @param \Reliese\Coders\Model\Model $pivot
     * @param \Reliese\Coders\Model\Model $reference
     */
    public function __construct(Fluent $parentCommand, Fluent $referenceCommand, Model $parent, Model $pivot, Model $reference)
    {
        $this->parentCommand = $parentCommand;
        $this->referenceCommand = $referenceCommand;
        $this->parent = $parent;
        $this->pivot = $pivot;
        $this->reference = $reference;
    }

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
        if ($this->parent->usesSnakeAttributes()) {
            return Str::snake(Str::plural(Str::singular($this->reference->getTable())));
        }

        return Str::camel(Str::plural(Str::singular($this->reference->getTable())));
    }

    /**
     * @return string
     */
    public function body()
    {
        $body = 'return $this->belongsToMany(';

        $body .= $this->reference->getQualifiedUserClassName().'::class';

        if ($this->needsPivotTable()) {
            $body .= ', '.Dumper::export($this->pivotTable());
        }

        if ($this->needsForeignKey()) {
            $body .= ', '.Dumper::export($this->foreignKey());
        }

        if ($this->needsOtherKey()) {
            $body .= ', '.Dumper::export($this->otherKey());
        }

        $body .= ')';

        $fields = $this->getPivotFields();

        if (! empty($fields)) {
            $body .= "\n\t\t\t\t\t->withPivot(".$this->parametrize($fields).')';
        }

        if ($this->pivot->usesTimestamps()) {
            $body .= "\n\t\t\t\t\t->withTimestamps()";
        }

        $body .= ';';

        return $body;
    }

    /**
     * @return bool
     */
    protected function needsPivotTable()
    {
        $models = [$this->referenceRecordName(), $this->parentRecordName()];
        sort($models);
        $defaultPivotTable = strtolower(implode('_', $models));

        return $this->pivotTable() != $defaultPivotTable || $this->needsForeignKey();
    }

    /**
     * @return mixed
     */
    protected function pivotTable()
    {
        if ($this->parent->getSchema() != $this->pivot->getSchema()) {
            return $this->pivot->getQualifiedTable();
        }

        return $this->pivot->getTable();
    }

    /**
     * @return bool
     */
    protected function needsForeignKey()
    {
        $defaultForeignKey = $this->parentRecordName().'_id';

        return $this->foreignKey() != $defaultForeignKey || $this->needsOtherKey();
    }

    /**
     * @return string
     */
    protected function foreignKey()
    {
        return $this->parentCommand->columns[0];
    }

    /**
     * @return bool
     */
    protected function needsOtherKey()
    {
        $defaultOtherKey = $this->referenceRecordName().'_id';

        return $this->otherKey() != $defaultOtherKey;
    }

    /**
     * @return string
     */
    protected function otherKey()
    {
        return $this->referenceCommand->columns[0];
    }

    private function getPivotFields()
    {
        return array_diff(array_keys($this->pivot->getProperties()), [
            $this->foreignKey(),
            $this->otherKey(),
            $this->pivot->getCreatedAtField(),
            $this->pivot->getUpdatedAtField(),
        ]);
    }

    /**
     * @return string
     */
    protected function parentRecordName()
    {
        // We make sure it is snake case because Eloquent assumes it is.
        return Str::snake($this->parent->getRecordName());
    }

    /**
     * @return string
     */
    protected function referenceRecordName()
    {
        // We make sure it is snake case because Eloquent assumes it is.
        return Str::snake($this->reference->getRecordName());
    }

    /**
     * @param array $fields
     *
     * @return string
     */
    private function parametrize($fields = [])
    {
        return (string) implode(', ', array_map(function ($field) {
            return Dumper::export($field);
        }, $fields));
    }
}
