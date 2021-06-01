<?php

namespace Reliese\MetaCode\Definition;

use Illuminate\Support\Str;
use Reliese\MetaCode\Format\IndentationProvider;
use Reliese\MetaCode\Tool\ClassNameTool;
use function sprintf;
/**
 * Class TryBlockDefinition
 */
class TryBlockDefinition extends StatementBlockDefinition
{
    public function __construct()
    {
        parent::__construct(
            new RawStatementDefinition("try"),
            null
        );
    }

    public function addCatchStatements(\Reliese\MetaCode\Enum\PhpTypeEnum $exceptionType,
        string $exceptionVariableName,
        StatementDefinitionCollectionInterface $catchStatementDefinitionCollection
    ): static {
        $catchStatement = new RawStatementDefinition(
            sprintf(
                "catch (%s \$%s) ",
                ClassNameTool::fullyQualifiedName($exceptionType->toDeclarationType()),
                $exceptionVariableName
            )
        );

        $this->setBlockSuffixStatement(
            (new StatementBlockDefinition(
                $catchStatement,
                $catchStatementDefinitionCollection
            ))
        );

        return $this;
    }
}
