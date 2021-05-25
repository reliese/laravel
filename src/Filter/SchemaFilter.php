<?php

namespace Reliese\Filter;

use InvalidArgumentException;
use function is_null;

/**
 * Class SchemaFilter
 */
class SchemaFilter
{
    protected const SCHEMA_STRING_FILTERS_INDEX = 'schemaStringFilters';
    protected const TABLE_STRING_FILTERS_INDEX = 'tableStringFilters';
    protected const COLUMN_STRING_FILTERS_INDEX = 'columnStringFilters';

    private bool $includeByDefault;

    public function __construct(bool $includeByDefault)
    {
        $this->includeByDefault = $includeByDefault;
    }

    public function excludeSchema(array $schemaMatchExpressions): static
    {
        if ($this->includeByDefault) {
            $this->addException($schemaMatchExpressions);
        }

        return $this;
    }

    public function isIncludeByDefault(): bool
    {
        return $this->includeByDefault;
    }

    private array $exceptions = [];

    public function addException(
        array $schemaMatchExpressions,
        ?array $tableMatchExpressions = null,
        ?array $columnMatchExpressions = null,
    ): static
    {
        if (empty($schemaMatchExpressions)) {
            throw new InvalidArgumentException("An exception case must specify at least one schema condition.");
        }

        // IsIncluded should be true when there is a match
        $schemaMatchFilter = new StringFilter(false);
        foreach ($schemaMatchExpressions as $schemaMatchExpression) {
            if (empty($schemaMatchExpression)) {
                throw new InvalidArgumentException("Include conditions require a schema filter. To apply the include condition to all schemas use the schema filter ['/^.*$/'] which will match on all possible schemas.");
            }
            $schemaMatchFilter->addException($schemaMatchExpression);
        }

        $tableMatchFilter = null;
        if (!is_null($tableMatchExpressions)) {
            // IsIncluded should be true when there is a match
            $tableMatchFilter = new StringFilter(false);
            foreach ($tableMatchExpressions as $tableMatchExpression) {
                $tableMatchFilter->addException($tableMatchExpression);
            }
        }

        $columnMatchFilter = null;
        if (!is_null($columnMatchExpressions)) {
            if (is_null($tableMatchExpressions)) {
                throw new InvalidArgumentException("A column exception cannot be specified without a table filter. To apply the column exception to all tables use the table exception ['/^.*$/'].");
            }
            // IsIncluded should be true when there is a match
            $columnMatchFilter = new StringFilter(false);
            foreach ($columnMatchExpressions as $columnMatchExpression) {
                $columnMatchFilter->addException($columnMatchExpression);
            }
        }

        $this->exceptions[] = [
            static::SCHEMA_STRING_FILTERS_INDEX => $schemaMatchFilter,
            static::TABLE_STRING_FILTERS_INDEX => $tableMatchFilter,
            static::COLUMN_STRING_FILTERS_INDEX => $columnMatchFilter];

        return $this;
    }

    public function isExcludedSchema(string $schemaName) : bool
    {
        return !$this->isIncludedSchema($schemaName);
    }

    public function isIncludedSchema(string $schemaName) : bool
    {
        if (empty($this->exceptions)) {
            return $this->includeByDefault;
        }

        foreach ($this->exceptions as $condition) {
            if (empty($condition[static::SCHEMA_STRING_FILTERS_INDEX])) {
                // schema match requires schema filter
                continue;
            }

            if (!empty($condition[static::TABLE_STRING_FILTERS_INDEX])) {
                // matching on an entire schema requires the condition not specify a table filter
                continue;
            }

            if (!empty($condition[static::COLUMN_STRING_FILTERS_INDEX])) {
                // matching on an entire schema requires the condition not specify a column filter
                continue;
            }

            /** @var ?StringFilter $schemaStringFilter */
            $schemaStringFilter = $condition[static::SCHEMA_STRING_FILTERS_INDEX];

            if ($schemaStringFilter->isIncluded($schemaName)) {
                // this table matches on schema, and table, so invert the default response
                return !$this->includeByDefault;
            }
        }

        return $this->includeByDefault;
    }

    public function isExcludedTable(string $schemaName, string $tableName) : bool
    {
        return !$this->isIncludedTable($schemaName, $tableName);
    }

    public function isIncludedTable(string $schemaName, string $tableName) : bool
    {
        if (empty($this->exceptions)) {
            return $this->includeByDefault;
        }

        foreach ($this->exceptions as $condition) {
            if (empty($condition[static::SCHEMA_STRING_FILTERS_INDEX])) {
                // table match requires schema filter
                continue;
            }

            if (empty($condition[static::TABLE_STRING_FILTERS_INDEX])) {
                // matching on an entire table requires a table filter
                continue;
            }

            if (!empty($condition[static::COLUMN_STRING_FILTERS_INDEX])) {
                // matching on an entire table requires the condition not specify a column filter
                continue;
            }

            /** @var ?StringFilter $schemaStringFilter */
            $schemaStringFilter = $condition[static::SCHEMA_STRING_FILTERS_INDEX];

            /** @var ?StringFilter $tableStringFilter */
            $tableStringFilter = $condition[static::TABLE_STRING_FILTERS_INDEX];

            if ($schemaStringFilter->isIncluded($schemaName)
                && $tableStringFilter->isIncluded($tableName)
            ) {
                // this table matches on schema, and table, so invert the default response
                return !$this->includeByDefault;
            }
        }

        return $this->includeByDefault;
    }

    public function isExcludedColumn(string $schemaName, string $tableName, string $columnName) : bool
    {
        return !$this->isIncludedColumn($schemaName, $tableName, $columnName);
    }

    public function isIncludedColumn(string $schemaName, string $tableName, string $columnName) : bool
    {
        if (empty($this->exceptions)) {
            return $this->includeByDefault;
        }

        foreach ($this->exceptions as $condition) {
            if (empty($condition[static::SCHEMA_STRING_FILTERS_INDEX])) {
                // column match requires schema filter
                continue;
            }

            if (empty($condition[static::TABLE_STRING_FILTERS_INDEX])) {
                // column match requires a table filter
                continue;
            }

            if (empty($condition[static::COLUMN_STRING_FILTERS_INDEX])) {
                // column match requires a column filter
                continue;
            }

            /** @var ?StringFilter $schemaStringFilter */
            $schemaStringFilter = $condition[static::SCHEMA_STRING_FILTERS_INDEX];

            /** @var ?StringFilter $tableStringFilter */
            $tableStringFilter = $condition[static::TABLE_STRING_FILTERS_INDEX];

            /** @var ?StringFilter $columnStringFilter */
            $columnStringFilter = $condition[static::COLUMN_STRING_FILTERS_INDEX];

            if ($schemaStringFilter->isIncluded($schemaName)
                && $tableStringFilter->isIncluded($tableName)
                && $columnStringFilter->isIncluded($columnName)
            ) {
                // this table matches on schema, table, and column, so invert the default response
                return !$this->includeByDefault;
            }
        }

        return $this->includeByDefault;
    }
}
