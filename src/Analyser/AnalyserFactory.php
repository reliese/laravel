<?php

namespace Reliese\Analyser;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Reliese\Analyser\Doctrine\DoctrineDatabaseAnalyser;
use Reliese\Configuration\RelieseConfiguration;
use RuntimeException;

/**
 * Class AnalyserFactory
 */
class AnalyserFactory
{
    /**
     * @var ConnectionFactory
     */
    private ConnectionFactory $connectionFactory;

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
     * @param ConnectionFactory $connectionFactory
     * @param Container $container
     */
    public function __construct(
        DatabaseManager $databaseManager,
        ConnectionFactory $connectionFactory,
        Container $container
    )
    {
        $this->databaseManager = $databaseManager;
        $this->connectionFactory = $connectionFactory;
        $this->container = $container;
    }

    /**
     * @param RelieseConfiguration $relieseConfiguration
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


//        $connectionClass = \get_class($connection);
//        switch ($connectionClass) {
//            case \Larapack\DoctrineSupport\Connections\MySqlConnection::class:
//            case MySqlConnection::class:
//                $doctrineDatabaseAssistantClass = new MySqlDoctrineDatabaseAssistant(
//                    $databaseBlueprintConfiguration,
//                    $databaseAnalyserConfiguration,
//                    $this->databaseManager,
//                    $this->connectionFactory,
//                    $connection
//                );
//                return new DoctrineDatabaseAnalyser($doctrineDatabaseAssistantClass);
//            case SQLiteConnection::class:
//            case PostgresConnection::class:
//            default:
//                throw new \RuntimeException("Unable to locate a \"$connectionClass\" compatible implementation of " . DatabaseAnalyserInterface::class);
//        }
    }
//
//    /**
//     * @param ConnectionInterface $connection
//     * @param OutputInterface $output
//     *
//     * @return DoctrineDatabaseAnalyser
//     */
//    protected function doctrineDatabaseAnalyserForMySql(
//        ConnectionInterface $connection
//    ) : DoctrineDatabaseAnalyser
//    {
//        $schemaFilter = $this->relieseConfiguration->getDatabaseAnalyserConfiguration()->getSchemaFilter();
//        if ($schemaFilter->isIncludeByDefault()) {
//            $schemaFilter->addException(['information_schema', 'mysql', 'performance_schema']);
//        }
//
//        $typeMappings = ['enum' => 'string'];
//
//        $doctrineSchemaManagerConfigurationDelegate = function(AbstractSchemaManager $doctrineSchemaManager) use ($typeMappings) : AbstractSchemaManager {
//            $platform = $doctrineSchemaManager->getDatabasePlatform();
//
//            foreach ($typeMappings as $databaseType => $phpType) {
//                $platform->registerDoctrineTypeMapping($databaseType, $phpType);
//            }
//
//            return $doctrineSchemaManager;
//        };
//
//        $result = new DoctrineDatabaseAnalyser(
//            $this->relieseConfiguration->getDatabaseAnalyserConfiguration(),
//            $doctrineSchemaManagerConfigurationDelegate,
//            $this->connectionFactory,
//            $connection
//        );
//
//        return $result;
//    }
}
