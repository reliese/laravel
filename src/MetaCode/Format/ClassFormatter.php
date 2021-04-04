<?php

namespace Reliese\MetaCode\Format;

use Illuminate\Support\Str;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
use Reliese\MetaCode\Definition\FunctionParameterDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Enum\PhpTypeEnum;

/**
 * Class ClassFormatter
 */
class ClassFormatter
{
    public function format(ClassDefinition $classDefinition): string
    {
        $indentationLevel = 0;
        $lines = [];

        $lines[] = "<?php\n\n";
        $lines[] = 'namespace ' . $classDefinition->getNamespace() . ";\n\n";
        $lines[] = "/**\n";
        $lines[] = ' * Class ' . $classDefinition->getClassName() . "\n";
        $lines[] = " * \n";
        $lines[] = " * Created by Reliese\n";
        $lines[] = " */\n";
        $lines[] = 'class ' . $classDefinition->getClassName() . "\n";
        $lines[] = "{\n";

        $properties = [];
        foreach ($classDefinition->getProperties() as $property) {
            $properties[] = $this->formatProperty($property, $indentationLevel + 1);
        }

        if (count($properties) > 0) {
            $lines[] = implode("\n", $properties) . "\n";
        }

        foreach ($classDefinition->getProperties() as $property) {
            if ($property->hasSetter()) {
                $this->appendSetter($property, $classDefinition);
            }
            if ($property->hasGetter()) {
                $this->appendGetter($property, $classDefinition);
            }
        }

        $methods = [];
        foreach ($classDefinition->getMethods() as $method) {
            $methods[] = $this->formatMethod($method, $indentationLevel + 1);
        }

        if (count($methods) > 0) {
            if (count($properties) > 0) {
                $lines[] = "\n";
            }

            $lines[] = implode("\n\n", $methods) . "\n";
        }

        $lines[] = "}\n";

        return implode('', $lines);
    }

    /**
     * @return string
     */
    private function getIndentationString(): string
    {
        return '    ';
    }

    private function formatProperty(ClassPropertyDefinition $property, int $indentationLevel): string
    {
        return str_repeat($this->getIndentationString(), $indentationLevel)
                . $property->getVisibilityEnum()->toReservedWord()
                . ' '
                . $property->getPhpTypeEnum()->toDeclarationType()
                . ' $'
                . $property->getVariableName()
                . ';';
    }

    private function formatMethod(ClassMethodDefinition $method, int $indentationLevel): string
    {
        $signature = str_repeat($this->getIndentationString(), $indentationLevel);

        if ($method->getAbstractEnum()->isAbstract()) {
            $signature .= $method->getAbstractEnum()->toReservedWord() . ' ';
        }

        if ($method->getVisibilityEnum()) {
            $signature .= $method->getVisibilityEnum()->toReservedWord() . ' ';
        }

        $signature .= 'function ' . $method->getFunctionName() . '(';

        $parameters = [];
        foreach ($method->getFunctionParameterDefinitions() as $parameter) {
            $parameters[] = $parameter->getParameterType()->toDeclarationType()
                . ' $'
                . $parameter->getParameterName();
        }

        $signature .= implode(', ', $parameters);

        $signature .= '): ';
        $signature .= $method->getReturnPhpTypeEnum()->toDeclarationType();
        $signature .= "\n";

        $signature .= str_repeat($this->getIndentationString(), $indentationLevel) . "{\n";

        $blockIndentationLevel = $indentationLevel + 1;
        foreach ($method->getBlockStatements() as $statement) {
            $signature .= str_repeat($this->getIndentationString(), $blockIndentationLevel)
                . $statement->toPhpCode()
                . "\n";
        }


        $signature .= str_repeat($this->getIndentationString(), $indentationLevel) . '}';

        return $signature;
    }

    /**
     * @param ClassPropertyDefinition $property
     * @param ClassDefinition $classDefinition
     */
    private function appendSetter(
        ClassPropertyDefinition $property,
        ClassDefinition $classDefinition
    )
    {
        $param = new FunctionParameterDefinition($property->getVariableName(), $property->getPhpTypeEnum());
        $getter = new ClassMethodDefinition('set' . Str::studly($property->getVariableName()),
            PhpTypeEnum::staticTypeEnum(),
            [
                $param
            ]);
        $getter->appendBodyStatement(new RawStatementDefinition('$this->' . $property->getVariableName() . ' = $' . $property->getVariableName() . ";\n"))
               ->appendBodyStatement(new RawStatementDefinition('return $this;'))
        ;

        $classDefinition->addMethodDefinition($getter);
    }

    /**
     * @param ClassPropertyDefinition $property
     * @param ClassDefinition $classDefinition
     */
    private function appendGetter(ClassPropertyDefinition $property, ClassDefinition $classDefinition): void
    {
        $getter = new ClassMethodDefinition('get' . Str::studly($property->getVariableName()),
            $property->getPhpTypeEnum());
        $getter->appendBodyStatement(new RawStatementDefinition('return $this->' . $property->getVariableName() . ';'));

        $classDefinition->addMethodDefinition($getter);
    }
}
