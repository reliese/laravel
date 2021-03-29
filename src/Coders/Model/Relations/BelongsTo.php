<?php

/**
 * Created by Cristian.
 * Date: 05/09/16 11:41 PM.
 */

namespace Reliese\Coders\Model\Relations;

use Illuminate\Support\Str;
use Reliese\Support\Dumper;
use Reliese\Coders\Model\Model;
use Reliese\Coders\Model\Relation;

class BelongsTo implements Relation
{
    /**
     * @var \Reliese\Meta\Relation
     */
    protected $command;

    /**
     * @var Model
     */
    protected $parent;

    /**
     * @var Model
     */
    protected $related;

    /**
     * BelongsToWriter constructor.
     *
     * @param \Reliese\Meta\Relation $command
     * @param Model $parent
     * @param Model $related
     */
    public function __construct(\Reliese\Meta\Relation $command, Model $parent, Model $related)
    {
        $this->command = $command;
        $this->parent = $parent;
        $this->related = $related;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        switch ($this->parent->getRelationNameStrategy()) {
            case 'foreign_key':
                $relationName = RelationHelper::stripSuffixFromForeignKey(
                    $this->parent->usesSnakeAttributes(),
                    $this->otherKey(),
                    $this->foreignKey()
                );
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
    public function body(): string
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
            foreach ($this->command->getReferences() as $index => $column) {
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
    public function hint(): string
    {
        $base =  $this->related->getQualifiedUserClassName();

        if ($this->isNullable()) {
            $base .= '|null';
        }

        return $base;
    }

    /**
     * @return bool
     */
    protected function needsForeignKey(): bool
    {
        $defaultForeignKey = $this->related->getRecordName().'_id';

        return $defaultForeignKey != $this->foreignKey() || $this->needsOtherKey();
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function foreignKey($index = 0): string
    {
        return $this->command->getColumns()[$index];
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function qualifiedForeignKey($index = 0): string
    {
        return $this->parent->getTable().'.'.$this->foreignKey($index);
    }

    /**
     * @return bool
     */
    protected function needsOtherKey(): bool
    {
        $defaultOtherKey = $this->related->getPrimaryKey();

        return $defaultOtherKey != $this->otherKey();
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function otherKey($index = 0): string
    {
        return $this->command->getReferences()[$index];
    }

    /**
     * @param int $index
     *
     * @return string
     */
    protected function qualifiedOtherKey($index = 0): string
    {
        return $this->related->getTable().'.'.$this->otherKey($index);
    }

    /**
     * Whether the "other key" is a composite foreign key.
     *
     * @return bool
     */
    protected function hasCompositeOtherKey(): bool
    {
        return count($this->command->getReferences()) > 1;
    }

    /**
     * @return bool
     */
    private function isNullable(): bool
    {
        return (bool) $this->parent
            ->getBlueprint()
                ->column($this->foreignKey())
                    ->isNullable();
    }
}
