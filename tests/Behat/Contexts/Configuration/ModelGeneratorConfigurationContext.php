<?php

namespace Tests\Behat\Contexts\Configuration;

use Reliese\Configuration\ModelGeneratorConfiguration;
use Tests\Behat\Contexts\FeatureContext;
use Tests\Test;

class ModelGeneratorConfigurationContext extends FeatureContext
{
    private ?ModelGeneratorConfiguration $modelGeneratorConfiguration;

    /**
     * @Given /^a default ModelGeneratorConfiguration$/
     */
    public function givenDefaultModelGeneratorConfiguration()
    {
        $this->modelGeneratorConfiguration = (new ModelGeneratorConfiguration())
            ->setPath(__DIR__.DIRECTORY_SEPARATOR.'void')
            ->setNamespace('App\Models')
            ->setClassSuffix('')
            ->setParentClassPrefix('Abstract')
            ->setParent(\Illuminate\Database\Eloquent\Model::class)
        ;
    }

    /**
     * @return ModelGeneratorConfiguration
     */
    public function getModelGeneratorConfiguration(): ModelGeneratorConfiguration
    {
        return $this->modelGeneratorConfiguration
            ??= $this->getConfigurationContexts()
                     ->getRelieseConfigurationContext()
                     ->getRelieseConfiguration()
                     ->getModelGeneratorConfiguration();
    }

    /**
     * @Given /^ModelGeneratorConfiguration directory is "([^"]*)"$/
     */
    public function givenModelGeneratorConfigurationDirectoryIs($path)
    {
        $this->getModelGeneratorConfiguration()->setPath($path);
    }

    /**
     * @Given /^ModelGeneratorConfiguration namespace is "([^"]*)"$/
     */
    public function givenModelGeneratorConfigurationNamespaceIs($namespace)
    {
        $this->getModelGeneratorConfiguration()->setNamespace($namespace);
    }

    /**
     * @Given /^ModelGeneratorConfiguration class suffix is "([^"]*)"$/
     */
    public function givenModelGeneratorConfigurationClassSuffixIs($classSuffix)
    {
        $this->getModelGeneratorConfiguration()->setClassSuffix($classSuffix);
    }

    /**
     * @Given /^ModelGeneratorConfiguration abstract class prefix is "([^"]*)"$/
     */
    public function givenModelGeneratorConfigurationParentClassPrefixIs($prefix)
    {
        $this->getModelGeneratorConfiguration()->setParentClassPrefix($prefix);
    }

    /**
     * @Given /^ModelGeneratorConfiguration parent is "([^"]*)"$/
     */
    public function givenModelGeneratorConfigurationParentIs($parent)
    {
        $this->getModelGeneratorConfiguration()->setParent($parent);
    }

    /**
     * @Given /^ModelGeneratorConfiguration uses trait "([^"]*)"$/
     */
    public function givenModelGeneratorConfigurationWithTraits($trait)
    {
        $this->getModelGeneratorConfiguration()->setTraits([$trait]);
    }
}