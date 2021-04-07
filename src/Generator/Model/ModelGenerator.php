<?php

namespace Reliese\Generator\Model;

use Reliese\Configuration\ModelGeneratorConfiguration;

/**
 * Class ModelGenerator
 */
class ModelGenerator
{
    private ModelGeneratorConfiguration $modelGeneratorConfiguration;

    public function __construct(ModelGeneratorConfiguration $modelGeneratorConfiguration)
    {
        $this->modelGeneratorConfiguration = $modelGeneratorConfiguration;
    }
}
