<?php


namespace Reliese\MetaCode\Definition;


interface ImportableInterface
{
    public function getFullyQualifiedImportableName(): string;

    public function getImportableName(): string;
}
