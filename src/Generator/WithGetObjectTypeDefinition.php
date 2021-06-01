<?php

namespace Reliese\Generator;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
use Reliese\MetaCode\Tool\ClassNameTool;

/**
 * Trait WithGetObjectTypeDefinition
 *
 * @package Reliese\Generator
 */
trait WithGetObjectTypeDefinition
{
    /** @var ObjectTypeDefinition[] */
    private array $generatedObjectTypeDefinitions = [];

    /**
     * @param ColumnOwnerInterface $columnOwner
     *
     * @return ObjectTypeDefinition
     */
    public function getObjectTypeDefinition(ColumnOwnerInterface $columnOwner): ObjectTypeDefinition
    {
        if (array_key_exists($columnOwner->getUniqueName(), $this->generatedObjectTypeDefinitions)) {
            return $this->generatedObjectTypeDefinitions[$columnOwner->getUniqueName()];
        }

        $objectTypeDefinition = $this->generateObjectTypeDefinition($columnOwner);

        $this->generatedObjectTypeDefinitions[$columnOwner->getUniqueName()] = $objectTypeDefinition;

        return $objectTypeDefinition;
    }

    /**
     * @param ColumnOwnerInterface $columnOwner
     *
     * @return ObjectTypeDefinition
     *
     * @internal This method should only be called via the getObjectTypeDefinition method defined in
     *           Generator/WithGeneratedObjectTypeDefinitions.php
     */
    protected function generateObjectTypeDefinition(ColumnOwnerInterface $columnOwner): ObjectTypeDefinition
    {
        $namespaceParts = explode(
            '\\',
            $this->getClassNamespace()
        );

        $namespaceParts[] = ClassNameTool::snakeCaseToClassName(
            $this->getClassPrefix(),
            $columnOwner->getName(),
            $this->getClassSuffix()
        );

        return new ObjectTypeDefinition(implode('\\', $namespaceParts));
    }

    /**
     * @return string
     */
    protected abstract function getClassNamespace(): string;

    /**
     * @return string
     */
    protected abstract function getClassPrefix(): string;

    /**
     * @return string
     */
    protected abstract function getClassSuffix(): string;
}