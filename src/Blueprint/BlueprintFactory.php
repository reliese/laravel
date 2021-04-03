<?php

namespace Reliese\Blueprint;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Reliese\Analyser\AnalyserFactory;
use Reliese\Coders\Model\Config;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseFactory
 */
class BlueprintFactory
{
    /**
     * @var AnalyserFactory
     */
    private $adapterFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DatabaseBlueprint[]
     */
    private $databaseBlueprints = [];

    /**
     * @var DatabaseBlueprint
     */
    private $laravelDatabaseManager;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * BlueprintFactory constructor.
     *
     * @param AnalyserFactory $analyserFactory
     * @param DatabaseManager $laravelDatabaseManager
     * @param Config          $config
     * @param OutputInterface $output
     */
    public function __construct(AnalyserFactory $analyserFactory,
        DatabaseManager $laravelDatabaseManager,
        Config $config,
        OutputInterface $output)
    {
        $this->laravelDatabaseManager = $laravelDatabaseManager;
        $this->config = $config;
        $this->adapterFactory = $analyserFactory;
        $this->output = $output;
    }

    /**
     * @param $connectionName
     *
     * @return DatabaseBlueprint
     */
    public function database($connectionName): DatabaseBlueprint
    {
        if (!empty($this->databaseBlueprints[$connectionName])) {
            return $this->databaseBlueprints[$connectionName];
        }

        $connection = $this->laravelDatabaseManager->connection($connectionName);
        $databaseAnalyser = $this->adapterFactory->databaseAnalyser(
            $connection,
            $this->output
        );
        $databaseBlueprint = $databaseAnalyser->analyseDatabase();

        if (!$this->isCompleteBlueprint($databaseBlueprint)) {
            throw new RuntimeException(get_class($this) . '->initializeBlueprint returned an incomplete blueprint. See console messages for details.');
        }

        return $this->databaseBlueprints[$connectionName] = $databaseBlueprint;
    }


    protected function isCompleteBlueprint(DatabaseBlueprint $databaseBlueprint)
    {
        /*
         * Has at least one schema
         */
        if (empty($databaseBlueprint->getSchemaNames())) {
            $this->output->writeln(sprintf("Blueprint for connection \"%s\" is incomplete because it does not contain at least one schema",
                $databaseBlueprint->getConnectionName()));
            return false;
        }

        return true;
    }
}
