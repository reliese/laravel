<?php

/**
 * Created by Cristian.
 * Date: 05/10/16 11:47 PM.
 */

namespace Reliese\Coders\Model\Relations;

use Illuminate\Support\Str;
use Reliese\Support\Dumper;
use Reliese\Coders\Model\Model;
use Reliese\Coders\Model\Relation;
use Illuminate\Database\Eloquent\Collection;

class BelongsToMany implements Relation
{
    /**
     * @var \Reliese\Meta\Relation
     */
    protected $parentCommand;

    /**
     * @var \Reliese\Meta\Relation
     */
    protected $referenceCommand;

    /**
     * @var Model
     */
    protected $parent;

    /**
     * @var Model
     */
    protected $pivot;

    /**
     * @var Model
     */
    protected $reference;

    /**
     * BelongsToMany constructor.
     *
     * @param \Reliese\Meta\Relation $parentCommand
     * @param \Reliese\Meta\Relation $referenceCommand
     * @param Model $parent
     * @param Model $pivot
     * @param Model $reference
     */
    public function __construct(
        \Reliese\Meta\Relation $parentCommand,
        \Reliese\Meta\Relation $referenceCommand,
        Model $parent,
        Model $pivot,
        Model $reference
    ) {
        $this->parentCommand = $parentCommand;
        $this->referenceCommand = $referenceCommand;
        $this->parent = $parent;
        $this->pivot = $pivot;
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function hint(): string
    {
        return '\\'.Collection::class.'|'.$this->reference->getQualifiedUserClassName().'[]';
    }

    /**
     * @return string
     */
    public function name(): string
    {
        $tableName = $this->reference->getTable(true);

        if ($this->parent->shouldLowerCaseTableName()) {
            $tableName = strtolower($tableName);
        }
        if ($this->parent->shouldPluralizeTableName()) {
            $tableName = Str::plural(Str::singular($tableName));
        }
        if ($this->parent->usesSnakeAttributes()) {
            return Str::snake($tableName);
        }

        return Str::camel($tableName);
    }

    /**
     * @return string
     */
    public function body(): string
    {
        $body = 'return $this->belongsToMany(';

        $body .= $this->reference->getQualifiedUserClassName().'::class';

        if ($this->needsPivotTable()) {
            $body .= ', '.Dumper::export($this->pivotTable());
        }

        if ($this->needsForeignKey()) {
            $foreignKey = $this->parent->usesPropertyConstants()
                ? $this->reference->getQualifiedUserClassName().'::'.strtoupper($this->foreignKey())
                : $this->foreignKey();
            $body .= ', '.Dumper::export($foreignKey);
        }

        if ($this->needsOtherKey()) {
            $otherKey = $this->reference->usesPropertyConstants()
                ? $this->reference->getQualifiedUserClassName().'::'.strtoupper($this->otherKey())
                : $this->otherKey();
            $body .= ', '.Dumper::export($otherKey);
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
    protected function needsPivotTable(): bool
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
    protected function needsForeignKey(): bool
    {
        $defaultForeignKey = $this->parentRecordName().'_id';

        return $this->foreignKey() != $defaultForeignKey || $this->needsOtherKey();
    }

    /**
     * @return string
     */
    protected function foreignKey(): string
    {
        return $this->parentCommand->getColumns()[0];
    }

    /**
     * @return bool
     */
    protected function needsOtherKey(): bool
    {
        $defaultOtherKey = $this->referenceRecordName().'_id';

        return $this->otherKey() != $defaultOtherKey;
    }

    /**
     * @return string
     */
    protected function otherKey(): string
    {
        return $this->referenceCommand->getColumns()[0];
    }

    private function getPivotFields(): array
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
    protected function parentRecordName(): string
    {
        // We make sure it is snake case because Eloquent assumes it is.
        return Str::snake($this->parent->getRecordName());
    }

    /**
     * @return string
     */
    protected function referenceRecordName(): string
    {
        // We make sure it is snake case because Eloquent assumes it is.
        return Str::snake($this->reference->getRecordName());
    }

    /**
     * @param array $fields
     *
     * @return string
     */
    private function parametrize($fields = []): string
    {
        return (string) implode(', ', array_map(function ($field) {
            $field = $this->reference->usesPropertyConstants()
                ? $this->pivot->getQualifiedUserClassName().'::'.strtoupper($field)
                : $field;

            return Dumper::export($field);
        }, $fields));
    }
}
