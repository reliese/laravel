<?php

namespace Reliese\Analyser;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\DatabaseManager;
use Reliese\Configuration\ConfigurationProfile;

/**
 * Class AnalyserFactory
 */
class AnalyserFactory
{
    /**
     * @var DatabaseManager
     */
    private DatabaseManager $databaseManager;

    /**
     * @var Container
     */
    private Container $container;

    /**
     * AnalyserFactory constructor.
     *
     * @param DatabaseManager $databaseManager
     * @param Container $container
     */
    public function __construct(
        DatabaseManager $databaseManager,
        Container $container
    )
    {
        $this->databaseManager = $databaseManager;
        $this->container = $container;
    }
}
