<?php

namespace Reliese\Analyser;

use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Configuration\DatabaseBlueprintConfiguration;

/**
 * Interface DatabaseAnalyserInterface
 */
interface DatabaseAnalyserInterface
{
    /**
     * @param DatabaseBlueprintConfiguration $databaseBlueprintConfiguration
     *
     * @return DatabaseBlueprint
     */
    public function analyseDatabase(DatabaseBlueprintConfiguration $databaseBlueprintConfiguration): DatabaseBlueprint;
}
