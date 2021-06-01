<?php

namespace Reliese\Command\Resolver;

use Reliese\Command\AbstractCodeGenerationCommand;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\DataTransport\DataTransportObjectAbstractClassGenerator;
use Reliese\Generator\DataTransport\DataTransportObjectClassGenerator;
use Reliese\Generator\Resolution\DataTransportFieldResolutionClassGenerator;
/**
 * Class GenerateDtoResolverCommand
 */
class GenerateDtoResolverCommand extends AbstractCodeGenerationCommand
{

    protected function initializeTableBasedCodeGenerators(): array
    {
        return [
            app(DataTransportFieldResolutionClassGenerator::class),
            app(DtoResolverAbstractClassGenerator::class),
        ];
    }

    protected function getCommandName(): string
    {
        return 'reliese:generate:dto-resolution';
    }

    protected function getCommandDescription(): string
    {
        return 'Generates Resolvers for Data Transport Objects';
    }
}