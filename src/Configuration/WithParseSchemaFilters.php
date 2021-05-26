<?php

namespace Reliese\Configuration;

use Reliese\Filter\SchemaFilter;
use const PHP_EOL;
/**
 * Trait WithParseSchemaFilters
 */
trait WithParseSchemaFilters
{
    protected function parseFilters(array &$configurationValues) : SchemaFilter
    {
        if (empty($configurationValues['Filters'])) {
            return new SchemaFilter(true);
        }

        $result =  new SchemaFilter(
            true === $configurationValues['Filters']['IncludeByDefault']
        );

        if (empty($configurationValues['Filters']['Except'])) {
            return $result;
        }

        foreach ($configurationValues['Filters']['Except'] as $exceptionCondition) {
            $schemaMatchExpressions = empty($exceptionCondition['schemas']) ? null : $exceptionCondition['schemas'];

            $tableMatchExpressions = empty($exceptionCondition['tables']) ? null : $exceptionCondition['tables'];

            $columnMatchExpressions = empty($exceptionCondition['columns']) ? null : $exceptionCondition['columns'];

            $result->addException($schemaMatchExpressions, $tableMatchExpressions, $columnMatchExpressions);
        }

        return $result;
    }
}