<?php

namespace Reliese\Command;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Reliese\Analyser\DatabaseAnalyserInterface;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Configuration\ConfigurationProfile;
use Reliese\Configuration\ConfigurationProfileFactory;
use Reliese\MetaCode\Writers\CodeWriter;
/**
 * Class AbstractCommand
 */
abstract class AbstractDatabaseAnalysisCommand extends Command
{
    protected Repository $config;

    /**
     * @var ConfigurationProfileFactory
     */
    protected ConfigurationProfileFactory $configurationProfileFactory;

    /**
     * AbstractCommand constructor.
     *
     * @param ConfigurationProfileFactory $configurationProfileFactory
     * @param Repository                  $config
     */
    public function __construct(
        ConfigurationProfileFactory $configurationProfileFactory,
        Repository $config
    ) {
        $commandTemplate = <<<END
%s %s
        {--p|profile=default : The name of the configuration profile from config/reliese.php}
        {--s|schema= : The name of the database schema}
        {--t|table= : Limits the code generation to classes based on specified table}
END;
        $this->signature = sprintf($commandTemplate, $this->getCommandName(), $this->getCommandDescription());

        parent::__construct();
        $this->configurationProfileFactory = $configurationProfileFactory;
        $this->config = $config;
    }

    protected abstract function getCommandName(): string;
    protected abstract function getCommandDescription(): string;

    /**
     * Execute the console command.
     *
     * @todo clean this method
     */
    public function handle()
    {
        /*
         * Set the active configuration profile
         */
        $this->configurationProfileFactory->setActiveConfigurationProfileByName($this->getConfigurationProfileName());

        /**
         * Create the correct analyser for the configuration profile
         *
         * @var DatabaseAnalyserInterface $databaseAnalyser
         */
        $databaseAnalyser = app()->make(DatabaseAnalyserInterface::class);

        /*
         * Allow the $databaseAnalyser to create the Database Blueprint
         */
        $databaseBlueprint = $databaseAnalyser->analyseDatabase();

        $this->processDatabaseBlueprint($databaseBlueprint);
    }

    protected abstract function processDatabaseBlueprint(DatabaseBlueprint $databaseBlueprint);

    /**
     * @return string
     */
    protected function getConfigurationProfileName()
    {
        return $this->option('profile');
    }

    /**
     * @return string
     */
    protected function getTable()
    {
        return $this->option('table');
    }

    /**
     * @return bool
     */
    protected function hasTableOption(): bool
    {
        return $this->hasOption('table') && !empty($this->getTable());
    }

    /**
     * @return bool
     */
    protected function hasSchemaOption(): bool
    {
        return $this->hasOption('schema');
    }

    /**
     * @return string
     */
    protected function getSchema()
    {
        $databaseConnectionName = $this->getActiveConfigurationProfile()->getDatabaseAnalyserConfiguration()
            ->getConnectionName();

        return $this->option('schema')
            ?: $this->config->get("database.connections.$databaseConnectionName.database");
    }

    protected function getActiveConfigurationProfile(): ConfigurationProfile
    {
        return $this->configurationProfileFactory->getActiveConfigurationProfile();
    }
}