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
    use WithParseSchemaFilters;
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
}
