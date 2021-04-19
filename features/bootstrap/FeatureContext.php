<?php

use Behat\Behat\Context\Context;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\IndexBlueprint;
use Reliese\Blueprint\SchemaBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\DatabaseBlueprintConfiguration;
use Reliese\Configuration\ModelGeneratorConfiguration;
use Reliese\Generator\Model\ModelGenerator;
use function PHPUnit\Framework\assertTrue;

class FeatureContext implements Context
{
    private DatabaseBlueprintConfiguration $databaseBlueprintConfiguration;

    private DatabaseBlueprint $databaseBlueprint;

    private SchemaBlueprint $schemaBlueprint;

    private TableBlueprint $tableBlueprint;

    private ModelGeneratorConfiguration $modelGeneratorConfiguration;

    /**
     * Initializes context.
     */
    public function __construct()
    {
    }

    /**
     * @Given a TableBlueprint with an autoincrementing id and string title
     */
    public function aTableblueprintWithAnAutoincrementingIdAndStringTitle()
    {
        $this->databaseBlueprintConfiguration = new DatabaseBlueprintConfiguration([]);
        $this->databaseBlueprint = new DatabaseBlueprint($this->databaseBlueprintConfiguration);
        $this->schemaBlueprint = new SchemaBlueprint($this->databaseBlueprint, 'a_schema');
        $this->tableBlueprint = new TableBlueprint($this->schemaBlueprint, 'a_table');

        $idColumn = new ColumnBlueprint(
            $this->tableBlueprint,
            'id',
            'int',
            false,
            -1,
            -1,
            -1,
            true,
            false
        );
        $titleColumn = new ColumnBlueprint(
            $this->tableBlueprint,
            'title',
            'string',
            false,
            -1,
            -1,
            -1,
            true,
            false
        );
        $primaryKey = new IndexBlueprint(
            $this->tableBlueprint,
            'primary_key',
            [$idColumn],
            true,
            false
        );

        $this->tableBlueprint->addColumnBlueprint($idColumn);
        $this->tableBlueprint->addColumnBlueprint($titleColumn);
        $this->tableBlueprint->addIndexBlueprint($primaryKey);
    }

    /**
     * @Given a ModelGeneratorConfiguration
     */
    public function aModelgeneratorconfiguration()
    {
        $this->modelGeneratorConfiguration = new ModelGeneratorConfiguration([
            'Path' => __DIR__.DIRECTORY_SEPARATOR.'void',
            'Namespace' => 'App\Models',
            'ClassSuffix' => '',
            'ParentClassPrefix' => 'Abstract',
            'Parent' => Illuminate\Database\Eloquent\Model::class,
            'use' => [],
            'connection' => false,
            'timestamps' => true,
            'soft_deletes' => true,
            'date_format' => 'Y-m-d H:i:s',
            'per_page' => 15,
            'base_files' => false,
            'snake_attributes' => true,
            'indent_with_space' => 0,
            'qualified_tables' => false,
            'hidden' => [
                '*secret*', '*password', '*token',
            ],
            'guarded' => [
                // 'created_by', 'updated_by'
            ],
            'casts' => [
                '*_json' => 'json',
            ],
            // This section has moved to the Blueprint Scope section
            //            'except' => [
            //                'migrations',
            //            ],
            // This section has moved to the Blueprint Scope section
            //            'only' => [
            //                // 'users',
            //            ],
            'table_prefix' => '',
            'lower_table_name_first' => false,
            'model_names' => [
                // example to rename the database table logins as the User model...
                // ['schema' => $all, 'table' => 'logins', 'model' => 'User']
            ],
            'relation_name_strategy' => 'foreign_key',
            'with_property_constants' => true,
            'pluralize' => true,
            'override_pluralize_for' => [],
        ]);
    }

    /**
     * @When /^I run fromTableBlueprint$/
     */
    public function iRunFromTableBlueprint()
    {
        $modelGenerator = new ModelGenerator(
            $this->modelGeneratorConfiguration
        );

        $modelGenerator->fromTableBlueprint(
            $this->tableBlueprint
        );
    }

    /**
     * @Then I should get an new model class
     */
    public function iShouldGetAnNewModelClass()
    {
        assertTrue(true);
    }
}
