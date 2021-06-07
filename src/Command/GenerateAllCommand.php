<?php

namespace Reliese\Command;

use Reliese\Generator\DataAccess\DataAccessAbstractClassGenerator;
use Reliese\Generator\DataAccess\DataAccessClassGenerator;
use Reliese\Generator\DataMap\ModelDataMapAbstractClassGenerator;
use Reliese\Generator\DataMap\ModelDataMapAccessorGenerator;
use Reliese\Generator\DataMap\ModelDataMapClassGenerator;
use Reliese\Generator\DataTransport\DataTransportObjectAbstractClassGenerator;
use Reliese\Generator\DataTransport\DataTransportObjectClassGenerator;
use Reliese\Generator\Validator\DtoValidatorAbstractClassGenerator;
use Reliese\Generator\Validator\DtoValidatorAccessorGenerator;
use Reliese\Generator\Validator\DtoValidatorClassGenerator;
/**
 * Class GenerateAllCommand
 */
class GenerateAllCommand extends AbstractCodeGenerationCommand
{

    protected function initializeTableBasedCodeGenerators(): array
    {
        return [
            app(DataAccessAbstractClassGenerator::class),
            app(DataAccessClassGenerator::class),
            app(ModelDataMapAbstractClassGenerator::class),
            app(ModelDataMapClassGenerator::class),
            app(ModelDataMapAccessorGenerator::class),
            app(DataTransportObjectAbstractClassGenerator::class),
            app(DataTransportObjectClassGenerator::class),
            app(DtoValidatorAbstractClassGenerator::class),
            app(DtoValidatorClassGenerator::class),
            app(DtoValidatorAccessorGenerator::class),
        ];
    }

    protected function getCommandName(): string
    {
        return "reliese:generate:all";
    }

    protected function getCommandDescription(): string
    {
        return "Generates all types of class definitions in a single command.";
    }
}