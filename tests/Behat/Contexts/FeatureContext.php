<?php

namespace Tests\Behat\Contexts;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class FeatureContext implements Context
{
    protected DatabaseBlueprintConfigurationContext $databaseBlueprintConfigurationContext;

    protected SchemaBlueprintContext $schemaBlueprintContext;

    protected DatabaseBlueprintContext $databaseBlueprintContext;

    protected TableBlueprintContext $tableBlueprintContext;

    protected ModelGeneratorConfigurationContext $modelGeneratorConfigurationContext;

    protected ModelGeneratorContext $modelGeneratorContext;

    protected ClassDefinitionContext $classDefinitionContext;

    /** @BeforeScenario
     * This method allows contexts to reference other contexts
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->databaseBlueprintConfigurationContext = $environment->getContext(DatabaseBlueprintConfigurationContext::class);
        $this->databaseBlueprintContext = $environment->getContext(DatabaseBlueprintContext::class);
        $this->schemaBlueprintContext = $environment->getContext(SchemaBlueprintContext::class);
        $this->modelGeneratorConfigurationContext = $environment->getContext(ModelGeneratorConfigurationContext::class);
        $this->modelGeneratorContext = $environment->getContext(ModelGeneratorContext::class);
        $this->tableBlueprintContext = $environment->getContext(TableBlueprintContext::class);
        $this->classDefinitionContext = $environment->getContext(ClassDefinitionContext::class);
    }

//    /**
//     * @Then I should get an new model class
//     */
//    public function iShouldGetAnNewModelClass()
//    {
//        /**
//         * Model expectations
//         */
//
//        $classDefinition = $this->encodedModel->getModelClassDefinition();
//        assertEquals($classDefinition->getName(), 'User', 'With a singular class name');
//        assertStringStartsWith(
//            $this->modelGeneratorConfiguration->getNamespace(),
//            $classDefinition->getNamespace(),
//            'With configured namespace'
//        );
//        assertStringEndsWith(
//            $this->modelGeneratorConfiguration->getClassSuffix(),
//            $classDefinition->getName(),
//            'With class suffix'
//        );
//
//        /**
//         * Abstract Model expectations
//         */
//
//        $parentClassDefinition = $this->encodedModel->getAbstractClassDefinition();
//        assertStringStartsWith(
//            $this->modelGeneratorConfiguration->getNamespace(),
//            $parentClassDefinition->getNamespace(),
//            'With configured namespace for parent'
//        );
//        assertStringStartsWith(
//            $this->modelGeneratorConfiguration->getParentClassPrefix(),
//            $parentClassDefinition->getName(),
//            'With configured parent prefix'
//        );
//
//        foreach ($parentClassDefinition->getConstants() as $constantDefinition) {
//            assertContains($constantDefinition->getName(), ['ID', 'TITLE'], 'With property constants');
//        }
//    }
}
