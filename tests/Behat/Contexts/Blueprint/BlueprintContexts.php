<?php

namespace Tests\Behat\Contexts\Blueprint;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Tests\Behat\Contexts\FeatureContext;
/**
 * Class BlueprintContexts
 */
class BlueprintContexts extends FeatureContext
{
    private SchemaBlueprintContext $schemaBlueprintContext;

    private DatabaseBlueprintContext $databaseBlueprintContext;

    private TableBlueprintContext $tableBlueprintContext;


    /** @BeforeScenario
     * This method allows contexts to reference other contexts
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        parent::gatherContexts($scope);
        $environment = $scope->getEnvironment();

        $this->databaseBlueprintContext = $environment->getContext(DatabaseBlueprintContext::class);
        $this->schemaBlueprintContext = $environment->getContext(SchemaBlueprintContext::class);
        $this->tableBlueprintContext = $environment->getContext(TableBlueprintContext::class);
    }

    /**
     * @return SchemaBlueprintContext
     */
    public function getSchemaBlueprintContext(): SchemaBlueprintContext
    {
        return $this->schemaBlueprintContext;
    }

    /**
     * @return DatabaseBlueprintContext
     */
    public function getDatabaseBlueprintContext(): DatabaseBlueprintContext
    {
        return $this->databaseBlueprintContext;
    }

    /**
     * @return TableBlueprintContext
     */
    public function getTableBlueprintContext(): TableBlueprintContext
    {
        return $this->tableBlueprintContext;
    }
}