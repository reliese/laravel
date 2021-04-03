<?php

namespace Reliese\MetaCode\Format;

use Reliese\Generator\DataTransport\ClassDefinition;
/**
 * Class ClassFileFormatter
 */
class ClassFormatter
{
    /**
     * @var ClassDefinition
     */
    private ClassDefinition $classDefinition;

    private string $destinationFilePath;

    public function __construct(
        ClassDefinition $classDefinition,
        string $templatePath = 'DefaulteTemplatePath'
    ) {
        $this->classDefinition = $classDefinition;
        $this->destinationFilePath = $destinationFilePath;
    }

    public function format(): string
    {
        // use templating library to format string
        $cd  = new ClassDefinition($className, $namespace);

        $cf = new ClassFormatter($cd);

        eval($cf->format());
    }
}
