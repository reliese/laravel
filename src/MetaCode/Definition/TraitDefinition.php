<?php

namespace Reliese\MetaCode\Definition;

/**
 * Class TraitDefinition
 */
class TraitDefinition extends ClassDefinition
{

    public function hasParentClass(): bool
    {
        return false;
    }

    public function getParentClassName() : string
    {
        return '';
    }

    /**
     * @return ClassTraitDefinition[]
     */
    public function getTraits(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getStructureType() : string {
        return 'trait';
    }
}
