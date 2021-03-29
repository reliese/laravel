<?php

/**
 * Created by Cristian.
 * Date: 04/10/16 11:32 PM.
 */

namespace Reliese\Coders\Model\Relations;

use Illuminate\Support\Str;
use Reliese\Coders\Model\Model;
use Reliese\Coders\Model\Relation;
use Reliese\Meta\RelationBag;

class ReferenceFactory
{
    /**
     * @var RelationBag
     */
    protected $related;

    /**
     * @var Model
     */
    protected $parent;

    /**
     * @var ForeignReference[]
     */
    protected $references = [];

    /**
     * ReferenceFactory constructor.
     *
     * @param RelationBag $related
     * @param Model $parent
     */
    public function __construct(RelationBag $related, Model $parent)
    {
        $this->related = $related;
        $this->parent = $parent;
    }

    /**
     * @return Relation[]
     */
    public function make(): array
    {
        if ($this->hasPivot()) {
            $relations = [];

            foreach ($this->references as $reference) {
                $relation = new BelongsToMany(
                    $this->related->getReference(),
                    $reference->getReference(),
                    $this->parent,
                    $this->related->getModel(),
                    $reference->getTarget()
                );
                $relations[$relation->name()] = $relation;
            }

            return $relations;
        }

        return [new HasOneOrManyStrategy(
            $this->related->getReference(),
            $this->parent,
            $this->related->getModel()
        )];
    }

    /**
     * @return bool
     */
    protected function hasPivot(): bool
    {
        $pivot = $this->related->getBlueprint()->table();
        $firstRecord = $this->parent->getRecordName();

        // See whether this potencial pivot table has the parent record name in it.
        // Not sure whether we should only take into account composite primary keys.
        if (
            ! Str::contains($pivot, $firstRecord)
        ) {
            return false;
        }

        $pivot = str_replace($firstRecord, '', $pivot);

        foreach ($this->related->getBlueprint()->relations() as $reference) {
            if ($reference == $this->related->getReference()) {
                continue;
            }

            $target = $this->related->getModel()->makeRelationModel($reference);

            // Check whether this potential pivot table has the target record name in it
            if (Str::contains($pivot, $target->getRecordName())) {
                $this->references[] = new ForeignReference(
                    $reference,
                    $target
                );
            }
        }

        return count($this->references) > 0;
    }
}
