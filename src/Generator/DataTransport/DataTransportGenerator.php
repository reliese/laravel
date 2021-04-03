<?php

namespace Reliese\Generator\DataTransport;

use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Configuration\DataTransportGenerationConfiguration;
/**
 * Class DataTransportGenerator
 */
class DataTransportGenerator
{
    /**
     * @var DataTransportGenerationConfiguration
     */
    private DataTransportGenerationConfiguration $dataTransportGenerationConfiguration;

    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    public function __construct(
        DatabaseBlueprint $databaseBlueprint,
        DataTransportGenerationConfiguration $dataTransportGenerationConfiguration
    ) {
        $this->databaseBlueprint = $databaseBlueprint;
        $this->dataTransportGenerationConfiguration = $dataTransportGenerationConfiguration;
    }

    public function generateFiles()
    {

    }
}
