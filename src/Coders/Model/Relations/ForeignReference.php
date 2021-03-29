<?php

namespace Reliese\Coders\Model\Relations;

use Reliese\Coders\Model\Model;
use Reliese\Meta\Relation;
class ForeignReference
{
    /**
     * @var Relation
     */
    private $reference;

    /**
     * @var Model
     */
    private $target;

    /**
     * ForeignReference constructor.
     * @param Relation $reference
     * @param Model $target
     */
    public function __construct(Relation $reference, Model $target)
    {
        $this->reference = $reference;
        $this->target = $target;
    }

    /**
     * @return Relation
     */
    public function getReference(): Relation
    {
        return $this->reference;
    }

    /**
     * @return Model
     */
    public function getTarget(): Model
    {
        return $this->target;
    }
}
