<?php

namespace Reliese\MetaCode\Format;

use Reliese\Configuration\ConfigurationProfile;
use Reliese\MetaCode\Definition\StatementDefinitionInterface;
use function get_class;
use function gettype;
use function implode;
/**
 * Class CodeFormatter
 */
class CodeFormatter
{
    /**
     * @var ClassFormatter
     */
    private ?ClassFormatter $classFormatter = null;

    /**
     * @var IndentationProvider
     */
    private IndentationProvider $indentationProvider;

    public function __construct(IndentationProvider $indentationProvider)
    {
        $this->indentationProvider = $indentationProvider;
    }

    /**
     * @param StatementDefinitionInterface|array $statementDefinitions
     *
     * @return string
     */
    public function formatStatementCollection(StatementDefinitionInterface|array $statementDefinitions): string
    {
        if ($statementDefinitions instanceof StatementDefinitionInterface) {
            $statementDefinitions = [$statementDefinitions];
        }

        $results = [];

        foreach ($statementDefinitions as $statementDefinition) {
            $results[] = trim($statementDefinition->toPhpCode($this->indentationProvider));
        }

        return implode("\n", $results);
    }

    /**
     * @return ClassFormatter
     */
    public function getClassFormatter(): ClassFormatter
    {
        return $this->classFormatter ??= new ClassFormatter($this->indentationProvider);
    }
}