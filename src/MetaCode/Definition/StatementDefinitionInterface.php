<?php

namespace Reliese\MetaCode\Definition;

interface StatementDefinitionInterface
{
    public function toPhpCode():string;
}
