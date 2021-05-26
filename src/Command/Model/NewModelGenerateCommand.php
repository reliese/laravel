<?php

namespace Reliese\Command\Model;

use Illuminate\Contracts\Config\Repository;
use Reliese\Command\AbstractGenerationCommand;
use Reliese\Configuration\RelieseConfiguration;
use Reliese\Generator\Model\ModelGenerator;

/**
 * Class NewModelGenerateCommand
 */
class NewModelGenerateCommand extends AbstractGenerationCommand
{
    /**
     * NewModelGenerateCommand constructor.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        parent::__construct(
            $config,
            'reliese:model:generate',
            'Generates Model Classes and regenerates model base classes.'
        );
    }

    public function initializeGenerator(RelieseConfiguration $relieseConfiguration)
    {
        return new ModelGenerator($relieseConfiguration);
    }
}
