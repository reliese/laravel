<?php

namespace Tests\Behat\Contexts;

use Reliese\Configuration\ModelGeneratorConfiguration;
use Tests\Test;

class ModelGeneratorConfigurationContext extends FeatureContext
{
    private ?ModelGeneratorConfiguration $modelGeneratorConfiguration;

    /**
     * @Given /^a default ModelGeneratorConfiguration$/
     */
    public function aDefaultModelGeneratorConfiguration()
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
        Test::assertInstanceOf(ModelGeneratorConfiguration::class, $this->modelGeneratorConfiguration);

        return $this->modelGeneratorConfiguration;
    }

    /**
     * @Given /^ModelGeneratorConfiguration directory is "([^"]*)"$/
     */
    public function modelgeneratorconfigurationDirectoryIs($modelPath)
    {
        $this->getModelGeneratorConfiguration()->setPath($modelPath);
    }
}