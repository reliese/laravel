<?php

/**
 * Created by Cristian.
 * Date: 11/09/16 09:26 PM.
 */

namespace Pursehouse\Modeler\Coders\Model\Relations;

use Illuminate\Support\Fluent;
use Pursehouse\Modeler\Coders\Model\Model;
use Pursehouse\Modeler\Coders\Model\Relation;

class HasOneOrManyStrategy implements Relation
{
    /**
     * @var \Pursehouse\Modeler\Coders\Model\Relation
     */
    protected $relation;

    /**
     * HasManyWriter constructor.
     *
     * @param \Illuminate\Support\Fluent  $command
     * @param \Pursehouse\Modeler\Coders\Model\Model $parent
     * @param \Pursehouse\Modeler\Coders\Model\Model $related
     */
    public function __construct(Fluent $command, Model $parent, Model $related)
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
    public function hint()
    {
        return $this->relation->hint();
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->relation->name();
    }

    /**
     * @return string
     */
    public function body()
    {
        return $this->relation->body();
    }
}
