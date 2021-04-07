<?php

namespace Reliese\Analyser;

use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Reliese\Analyser\Doctrine\DoctrineDatabaseAnalyser;
use Reliese\Analyser\Doctrine\MySqlDoctrineDatabaseAssistant;
use Reliese\Configuration\DatabaseAnalyserConfiguration;
use Reliese\Configuration\DatabaseBlueprintConfiguration;
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
     * AnalyserFactory constructor.
     *
     * @param DatabaseManager $databaseManager
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct(DatabaseManager $databaseManager, ConnectionFactory $connectionFactory)
    {
        $this->databaseManager = $databaseManager;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @param DatabaseAnalyserConfiguration $databaseAnalyserConfiguration
     *
     * @return DatabaseAnalyserInterface
     */
    public function databaseAnalyser(
        DatabaseBlueprintConfiguration $databaseBlueprintConfiguration,
        DatabaseAnalyserConfiguration $databaseAnalyserConfiguration
    ): DatabaseAnalyserInterface
    {
        $databaseAnalyserConnectionName = $databaseAnalyserConfiguration->getConnectionName();

        /*
         * Use the connection name to get an instance of the connecton, then check its type to determine which analyser
         * should be used
         */
        $connection = $this->databaseManager->connection($databaseAnalyserConnectionName);

        $connectionClass = \get_class($connection);
        switch ($connectionClass) {
            case \Larapack\DoctrineSupport\Connections\MySqlConnection::class:
            case MySqlConnection::class:
                $doctrineDatabaseAssistant = new MySqlDoctrineDatabaseAssistant(
                    $databaseBlueprintConfiguration,
                    $databaseAnalyserConfiguration,
                    $this->databaseManager,
                    $this->connectionFactory,
                    $connection
                );
                return new DoctrineDatabaseAnalyser($doctrineDatabaseAssistant);
            case SQLiteConnection::class:
            case PostgresConnection::class:
            default:
                throw new \RuntimeException("Unable to locate a \"$connectionClass\" compatible implementation of " . DatabaseAnalyserInterface::class);
        }
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
