<?php

namespace Reliese\Configuration;

use Illuminate\Support\Facades\Log;
use Reliese\Filter\SchemaFilter;
use Reliese\Filter\StringFilter;

/**
 * Class DatabaseBlueprintConfiguration
 */
class DatabaseBlueprintConfiguration
{
    /**
     * @var RelieseConfiguration
     */
    private RelieseConfiguration $relieseConfiguration;

    /**
     * @var SchemaFilter
     */
    private SchemaFilter $schemaFilter;

    /**
     * DatabaseBlueprintConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration) {
        $this->schemaFilter = $this->parseFilters($configuration);
        $this->parseAdditionalRelationships($configuration);
    }

    /**
     * @return SchemaFilter
     */
    public function getSchemaFilter(): SchemaFilter
    {
        return $this->schemaFilter;
    }

    private function parseAdditionalRelationships(array &$configurationValues): void
    {
        // TODO: AdditionalRelationships parsing is incomplete
    }

    private function parseFilters(array &$configurationValues) : SchemaFilter
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
