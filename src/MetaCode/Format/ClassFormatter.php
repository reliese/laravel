<?php

namespace Reliese\MetaCode\Format;

use Illuminate\Support\Str;
use Reliese\MetaCode\Definition\ClassConstantDefinition;
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
        $depth = 0;
        $lines = [];

        $this->prepareGettersAndSetters($classDefinition);

        $lines[] = "<?php\n\n";
        $lines[] = 'namespace ' . $classDefinition->getNamespace() . ";\n\n";
        $lines[] = "/**\n";
        $lines[] = ' * Class ' . $classDefinition->getClassName() . "\n";
        $lines[] = " * \n";
        $lines[] = " * Created by Reliese\n";
        $lines[] = " */\n";
        $lines[] = 'class ' . $classDefinition->getClassName() . "\n";
        $lines[] = "{\n";

        $body = [];

        $body[] = $this->formatConstants($classDefinition, $depth);
        $body[] = $this->formatProperties($classDefinition, $depth);
        $body[] = $this->formatMethods($classDefinition, $depth);

        // Filter away empty blocks and space them with one empty line
        $lines[] = implode("\n\n", array_filter($body));
        $lines[] = "\n}\n";

        return implode('', $lines);
    }

    /**
     * @param int $depth
     *
     * @return string
     */
    private function getIndentation(int $depth): string
    {
        return str_repeat($this->getIndentationSymbol(), $depth);
    }

    /**
     * @return string
     */
    private function getIndentationSymbol(): string
    {
        return '    ';
    }

    /**
     * @param ClassDefinition $classDefinition
     */
    private function prepareGettersAndSetters(ClassDefinition $classDefinition): void
    {
        foreach ($classDefinition->getProperties() as $property) {
            if ($property->hasSetter()) {
                $this->appendSetter($property, $classDefinition);
            }
            if ($property->hasGetter()) {
                $this->appendGetter($property, $classDefinition);
            }
        }
    }

    /**
     * @param ClassDefinition $classDefinition
     * @param int $depth
     *
     * @return string
     */
    private function formatConstants(ClassDefinition $classDefinition, int $depth): string
    {
        $constants = [];

        foreach ($classDefinition->getConstants() as $constant) {
            $constants[] = $this->formatConstant($constant, $depth + 1);
        }

        return implode("\n", $constants);
    }

    private function formatConstant(ClassConstantDefinition $constant, int $depth): string
    {
        return $this->getIndentation($depth)
            . $constant->getVisibilityEnum()->toReservedWord()
            . ' const '
            . $constant->getName()
            . ' = '
            . var_export($constant->getValue(), true)
            . ';';
    }

    /**
     * @param ClassDefinition $classDefinition
     * @param int $depth
     *
     * @return string
     */
    private function formatProperties(ClassDefinition $classDefinition, int $depth): string
    {
        $properties = [];

        foreach ($classDefinition->getProperties() as $property) {
            $properties[] = $this->formatProperty($property, $depth + 1);
        }

        return implode("\n", $properties);
    }

    private function formatProperty(ClassPropertyDefinition $property, int $depth): string
    {
        return $this->getIndentation($depth)
                . $property->getVisibilityEnum()->toReservedWord()
                . ' '
                . $property->getPhpTypeEnum()->toDeclarationType()
                . ' $'
                . $property->getVariableName()
                . ';';
    }

    /**
     * @param ClassPropertyDefinition $property
     * @param ClassDefinition $classDefinition
     */
    private function appendSetter(ClassPropertyDefinition $property, ClassDefinition $classDefinition)
    {
        $param = new FunctionParameterDefinition($property->getVariableName(), $property->getPhpTypeEnum());

        $getter = new ClassMethodDefinition('set' . Str::studly($property->getVariableName()),
            PhpTypeEnum::staticTypeEnum(),
            [
                $param
            ]
        );

        $getter->appendBodyStatement(new RawStatementDefinition(
                   '$this->' . $property->getVariableName() . ' = $' . $property->getVariableName() . ";\n"
               ))
               ->appendBodyStatement(new RawStatementDefinition(
                   'return $this;'
               ));

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

    /**
     * @param ClassDefinition $classDefinition
     * @param int $depth
     *
     * @return string
     */
    private function formatMethods(ClassDefinition $classDefinition, int $depth): string
    {
        $methods = [];

        foreach ($classDefinition->getMethods() as $method) {
            $methods[] = $this->formatMethod($method, $depth + 1);
        }

        return implode("\n\n", $methods);
    }

    private function formatMethod(ClassMethodDefinition $method, int $depth): string
    {
        $signature = $this->getIndentation($depth);

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

        $signature .= $this->getIndentation($depth) . "{\n";

        $blockDepth = $depth + 1;
        foreach ($method->getBlockStatements() as $statement) {
            $signature .= $this->getIndentation($blockDepth)
                . $statement->toPhpCode()
                . "\n";
        }


        $signature .= $this->getIndentation($depth) . '}';

        return $signature;
    }
}
