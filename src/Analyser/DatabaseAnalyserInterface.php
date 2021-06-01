<?php

namespace Reliese\Analyser;

use Reliese\Blueprint\DatabaseBlueprint;

/**
 * Interface DatabaseAnalyserInterface
 */
interface DatabaseAnalyserInterface
{
    /**
     * @return DatabaseBlueprint
     */
    public function analyseDatabase(): DatabaseBlueprint;
}
