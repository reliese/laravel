<?php

namespace Reliese\Generator;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\MetaCode\Definition\ClassDefinition;
use function array_key_exists;

/**
 * Trait WithGetClassDefinition
 *
 * @package Reliese\Generator
 */
trait WithGetClassDefinition
{
    /** @var ClassDefinition[] */
    private array $generatedClassDefinitions = [];

    /**
     * @param ColumnOwnerInterface $columnOwner
     *
     * @return ClassDefinition
     */
    public function getClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        if (array_key_exists($columnOwner->getUniqueName(), $this->generatedClassDefinitions)) {
            return $this->generatedClassDefinitions[$columnOwner->getUniqueName()];
        }

        $classDefinition = $this->generateClassDefinition($columnOwner);

        $this->generatedClassDefinitions[$columnOwner->getUniqueName()] = $classDefinition;

        return $classDefinition;
    }

    /**
     * @param ColumnOwnerInterface $columnOwner
     *
     * @return ClassDefinition
     */
    protected abstract function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition;
}