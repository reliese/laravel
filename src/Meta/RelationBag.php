<?php

namespace Reliese\Meta;

use Reliese\Coders\Model\Model;

class RelationBag
{
    /**
     * @var Blueprint
     */
    private $blueprint;

    /**
     * @var Relation
     */
    private $reference;

    /**
     * @var Model
     */
    private $model;

    /**
     * RelationBag constructor.
     *
     * @param Blueprint $blueprint
     * @param Relation  $reference
     */
    public function __construct(Blueprint $blueprint, Relation $reference)
    {
        $this->blueprint = $blueprint;
        $this->reference = $reference;
    }

    /**
     * @return Blueprint
     */
    public function getBlueprint(): Blueprint
    {
        return $this->blueprint;
    }

    /**
     * @return Relation
     */
    public function getReference(): Relation
    {
        return $this->reference;
    }

    public function withModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }
}
