<?php

namespace Reliese\MetaCode\Definition;

interface CodeDefinitionInterface
{
    public function getDirectory(): string;

    public function getFilePath(): string;
}