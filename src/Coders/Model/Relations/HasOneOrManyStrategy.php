<?php

/**
 * Created by Cristian.
 * Date: 11/09/16 09:26 PM.
 */

namespace Reliese\Coders\Model\Relations;

use Reliese\Coders\Model\Model;
use Reliese\Coders\Model\Relation;

class HasOneOrManyStrategy implements Relation
{
    /**
     * @var Relation
     */
    protected $relation;

    /**
     * HasManyWriter constructor.
     *
     * @param \Reliese\Meta\Relation $command
     * @param Model $parent
     * @param Model $related
     */
    public function __construct(\Reliese\Meta\Relation $command, Model $parent, Model $related)
    {
        if (
            $related->isPrimaryKey($command) ||
            $related->isUniqueKey($command)
        ) {
            $this->relation = new HasOne($command, $parent, $related);
        } else {
            $this->relation = new HasMany($command, $parent, $related);
        }
    }

    /**
     * @return string
     */
    public function hint(): string
    {
        return $this->relation->hint();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->relation->name();
    }

    /**
     * @return string
     */
    public function body(): string
    {
        return $this->relation->body();
    }
}
