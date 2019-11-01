<?php

namespace Pursehouse\Modeler\Coders\Model\Relations;

use Pursehouse\Modeler\Support\Dumper;
use Illuminate\Support\Fluent;
use Pursehouse\Modeler\Coders\Model\Model;
use Pursehouse\Modeler\Coders\Model\Relation;

abstract class HasOneOrMany implements Relation
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
     * HasManyWriter constructor.
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
    abstract public function hint();

    /**
     * @return string
     */
    abstract public function name();

    /**
     * @return string
     */
    public function body()
    {
        $body = 'return $this->'.$this->method().'(';

        $body .= $this->related->getQualifiedUserClassName().'::class';

        if ($this->needsForeignKey()) {
            $foreignKey = $this->parent->usesPropertyConstants()
                ? $this->related->getQualifiedUserClassName().'::'.strtoupper($this->foreignKey())
                : $this->foreignKey();
            $body .= ', '.Dumper::export($foreignKey);
        }

        if ($this->needsLocalKey()) {
            $localKey = $this->related->usesPropertyConstants()
                ? $this->related->getQualifiedUserClassName().'::'.strtoupper($this->localKey())
                : $this->localKey();
            $body .= ', '.Dumper::export($localKey);
        }

        $body .= ');';

        return $body;
    }

    /**
     * @return string
     */
    abstract protected function method();

    /**
     * @return bool
     */
    protected function needsForeignKey()
    {
        $defaultForeignKey = $this->parent->getRecordName().'_id';

        if ($this->parent->shouldQualifyTableName()) {
            $defaultForeignKey = $this->parent->getTable().'_'.$defaultForeignKey;
        }

        return $defaultForeignKey != $this->foreignKey() || $this->needsLocalKey();
    }

    /**
     * @return string
     */
    protected function foreignKey()
    {
        return $this->command->columns[0];
    }

    /**
     * @return bool
     */
    protected function needsLocalKey()
    {
        return $this->parent->getPrimaryKey() != $this->localKey();
    }

    /**
     * @return string
     */
    protected function localKey()
    {
        return $this->command->references[0];
    }
}
