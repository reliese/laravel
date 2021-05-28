<?php

namespace Reliese\Configuration;

use Reliese\Filter\DatabaseFilter;
use const PHP_EOL;
/**
 * Trait WithParseSchemaFilters
 */
trait WithDatabaseFilters
{
    /**
     * @return DatabaseFilter
     */
    public function getDatabaseFilters(): DatabaseFilter
    {
        return $this->databaseFilters;
    }

    /**
     * @param DatabaseFilter $databaseFilters
     *
     * @return $this
     */
    public function setDatabaseFilters(DatabaseFilter $databaseFilters): static
    {
        $this->databaseFilters = $databaseFilters;
        return $this;
    }
    protected ?DatabaseFilter $databaseFilters = null;

    protected function parseDatabaseFilters(array &$configurationValues) : static
    {
        if (empty($configurationValues['Filters'])) {
            return $this->setDatabaseFilters(new DatabaseFilter(true));
        }

        $result =  new DatabaseFilter(
            true === $configurationValues['Filters']['IncludeByDefault']
        );

        if (empty($configurationValues['Filters']['Except'])) {
            return $this->setDatabaseFilters($result);
        }

        foreach ($configurationValues['Filters']['Except'] as $exceptionCondition) {
            $schemaMatchExpressions = empty($exceptionCondition['schemas']) ? null : $exceptionCondition['schemas'];

            $tableMatchExpressions = empty($exceptionCondition['tables']) ? null : $exceptionCondition['tables'];

            $columnMatchExpressions = empty($exceptionCondition['columns']) ? null : $exceptionCondition['columns'];

            $result->addException($schemaMatchExpressions, $tableMatchExpressions, $columnMatchExpressions);
        }

        return $this->setDatabaseFilters($result);
    }
}