<?php

namespace Reliese\Analyser;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Reliese\Analyser\Doctrine\DoctrineAnalyserConfiguration;
use Reliese\Analyser\Doctrine\DoctrineDatabaseAnalyser;
use Reliese\Filter\StringFilter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AnalyserFactory
 */
class AnalyserFactory
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * AnalyserFactory constructor.
     *
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct(

        ConnectionFactory $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @return DatabaseAnalyserInterface
     */
    public function databaseAnalyser(ConnectionInterface $connection, OutputInterface $output): DatabaseAnalyserInterface
    {
        switch (get_class($connection)) {
            case \Larapack\DoctrineSupport\Connections\MySqlConnection::class:
            case MySqlConnection::class:
                return $this->doctrineDatabaseAnalyserForMySql($connection, $output);
            case SQLiteConnection::class:
                throw new \RuntimeException("Unable to locate a Postgress compatible implementation of " . DatabaseAnalyserInterface::class);
            case PostgresConnection::class:
                throw new \RuntimeException("Unable to locate a SqlLite compatible implementation of " . DatabaseAnalyserInterface::class);
        }

        throw new \RuntimeException(__METHOD__ . " does not define an implementation for " . DatabaseAnalyserInterface::class);
    }

    /**
     * @param ConnectionInterface $connection
     * @param OutputInterface $output
     *
     * @return DoctrineDatabaseAnalyser
     */
    protected function doctrineDatabaseAnalyserForMySql(
        ConnectionInterface $connection,
        OutputInterface $output
    ) : DoctrineDatabaseAnalyser
    {
        $schemaFilters = new StringFilter(true);
        $schemaFilters
            ->setMatchFilter('information_schema', false)
            ->setMatchFilter('mysql', false)
            ->setMatchFilter('performance_schema', false)
        ;

        $typeMappings = ['enum' => 'string'];

        $doctrineSchemaManagerConfigurationDelegate = function(AbstractSchemaManager $doctrineSchemaManager) use ($typeMappings) : AbstractSchemaManager {
            $platform = $doctrineSchemaManager->getDatabasePlatform();

            foreach ($typeMappings as $databaseType => $phpType) {
                $platform->registerDoctrineTypeMapping($databaseType, $phpType);
            }

            return $doctrineSchemaManager;
        };

        $result = new DoctrineDatabaseAnalyser(
            $schemaFilters,
            $doctrineSchemaManagerConfigurationDelegate,
            $this->connectionFactory,
            $connection,
            $output
        );

        return $result;
    }
}
