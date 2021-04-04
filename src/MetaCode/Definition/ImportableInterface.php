<?php


namespace Reliese\MetaCode\Definition;


interface ImportableInterface
{
    public function getFullyQualifiedName(): string;

    public function getFullyQualifiedImportableName(): string;

    public function getImportableName(): string;
}
