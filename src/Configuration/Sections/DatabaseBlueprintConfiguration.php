<?php

namespace Reliese\Configuration\Sections;

use Illuminate\Support\Facades\Log;
use Reliese\Configuration\WithDatabaseFilters;
use Reliese\Filter\DatabaseFilter;
use Reliese\Filter\StringFilter;

/**
 * Class DatabaseBlueprintConfiguration
 */
class DatabaseBlueprintConfiguration
{
    use WithDatabaseFilters;

    /**
     * DatabaseBlueprintConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration) {
        $this
            ->parseDatabaseFilters($configuration)
            ->parseAdditionalRelationships($configuration);
    }

    private function parseAdditionalRelationships(array &$configurationValues): static
    {
        //TODO: implement blueprint relationship backfilling
        return $this;
    }
}
