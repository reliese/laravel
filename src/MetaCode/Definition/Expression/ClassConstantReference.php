<?php

namespace Reliese\MetaCode\Definition\Expression;

use Reliese\MetaCode\Definition\ImportableInterface;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
use Reliese\MetaCode\Definition\StatementDefinitionInterface;
use Reliese\MetaCode\Format\IndentationProvider;
/**
 * Class ClassConstantReference
 */
class ClassConstantReference implements StatementDefinitionInterface, ImportableInterface
{
    /**
     * @var ObjectTypeDefinition
     */
    private ObjectTypeDefinition $classObjectTypeDefinition;

    private string $constantName;

    public function __construct(
        ObjectTypeDefinition $classObjectTypeDefinition,
        string $constantName
    ) {
        $this->classObjectTypeDefinition = $classObjectTypeDefinition;
        $this->constantName = $constantName;
    }

    public function toPhpCode(IndentationProvider $indentationProvider): string
    {
        return \sprintf("%s::%s",
            $this->getClassObjectTypeDefinition()->getImportableName(),
            $this->getConstantName()
        );
    }

    /**
     * @return ObjectTypeDefinition
     */
    public function getClassObjectTypeDefinition(): ObjectTypeDefinition
    {
        return $this->classObjectTypeDefinition;
    }

    /**
     * @return string
     */
    public function getConstantName(): string
    {
        return $this->constantName;
    }

    public function getFullyQualifiedName(): string
    {
        return $this->getClassObjectTypeDefinition()->getFullyQualifiedName();
    }

    public function getFullyQualifiedImportableName(): string
    {
        return $this->getClassObjectTypeDefinition()->getFullyQualifiedImportableName();
    }

    public function getImportableName(): string
    {
        return $this->getClassObjectTypeDefinition()->getImportableName();
    }
}