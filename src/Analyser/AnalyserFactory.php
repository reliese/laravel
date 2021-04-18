<?php

namespace Reliese\Analyser;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Reliese\Analyser\Doctrine\DoctrineDatabaseAnalyser;
use Reliese\Configuration\RelieseConfiguration;

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

    /**
     * @param RelieseConfiguration $relieseConfiguration
     * @todo handle exceptions
     *
     * @return DatabaseAnalyserInterface
     */
    public function databaseAnalyser(RelieseConfiguration $relieseConfiguration): DatabaseAnalyserInterface
    {
        $databaseAnalyserConfiguration = $relieseConfiguration->getDatabaseAnalyserConfiguration();
        $connectionName = $databaseAnalyserConfiguration->getConnectionName();
        $doctrineDatabaseAssistantClass = $databaseAnalyserConfiguration->getDoctrineDatabaseAssistantClass();

        /*
         * Use the connection name to get an instance of the connection, then check its type to determine which analyser
         * should be used
         */
        $connection = $this->databaseManager->connection($connectionName);

        $doctrineDatabaseAssistant = $this->container->make($doctrineDatabaseAssistantClass, [
            'relieseConfiguration' => $relieseConfiguration,
            'configuredConnection' => $connection,
        ]);

        return $this->container->make(DoctrineDatabaseAnalyser::class, [
            'doctrineDatabaseAssistant' => $doctrineDatabaseAssistant,
        ]);
    }
}
