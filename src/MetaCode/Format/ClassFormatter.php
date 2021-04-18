<?php

namespace Reliese\MetaCode\Format;

use Illuminate\Support\Str;
use Reliese\MetaCode\Definition\ClassConstantDefinition;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
use Reliese\MetaCode\Definition\ClassTraitDefinition;
use Reliese\MetaCode\Definition\FunctionParameterDefinition;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
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

        $parent = $this->formatParentClass($classDefinition);

        $body = [];

        $body[] = $this->formatTraits($classDefinition, $depth);
        $body[] = $this->formatConstants($classDefinition, $depth);
        $body[] = $this->formatProperties($classDefinition, $depth);
        $body[] = $this->formatMethods($classDefinition, $depth);

        $lines[] = "<?php\n\n";
        $lines[] = 'namespace ' . $classDefinition->getNamespace() . ";\n\n";

        $imports = $this->formatImports($classDefinition);

        if (!empty($imports)) {
            $lines[] = implode("\n", $imports) . "\n\n";
        }

        $lines[] = "/**\n";
        $lines[] = ' * Class ' . $classDefinition->getName() . "\n";
        $lines[] = " * \n";
        $lines[] = " * Created by Reliese\n";
        $lines[] = " */\n";
        $lines[] = 'class ' . $classDefinition->getName();

        if (!empty($parent)) {
            $lines[] = ' extends ' . $parent;
        }

        $lines[] = "\n";
        $lines[] = "{\n";

        // Filter away empty blocks and space them with one empty line
        $lines[] = implode("\n\n", array_filter($body));
        $lines[] = "\n}\n";

        return implode('', array_filter($lines));
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
     *
     * @return string
     */
    private function formatParentClass(ClassDefinition $classDefinition): string
    {
        $parent = '';

        if ($classDefinition->hasParentClass()) {
            $phpType = PhpTypeEnum::objectOfType($classDefinition->getParentClassName());
            $parent = $this->shortenTypeHint($classDefinition, $phpType);
        }

        return $parent;
    }

    /**
     * @param ClassDefinition $classDefinition
     * @param int $depth
     *
     * @return string
     */
    private function formatTraits(ClassDefinition $classDefinition, int $depth): string
    {
        $constants = [];

        foreach ($classDefinition->getTraits() as $trait) {
            $constants[] = $this->formatTrait($classDefinition, $trait, $depth + 1);
        }

        return implode("\n", $constants);
    }

    private function formatTrait(ClassDefinition $classDefinition, ClassTraitDefinition $trait, int $depth): string
    {
        $object = $trait->getFullyQualifiedName();

        if (!$classDefinition->willCollideImport($trait)) {
            $classDefinition->addImport($trait);
            $object = $trait->getName();
        }


        return $this->getIndentation($depth) . 'use ' . $object . ';';
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
            $properties[] = $this->formatProperty($classDefinition, $property, $depth + 1);
        }

        return implode("\n", $properties);
    }

    private function formatProperty(ClassDefinition $classDefinition, ClassPropertyDefinition $property, int $depth): string
    {
        return $this->getIndentation($depth)
                . $property->getVisibilityEnum()->toReservedWord()
                . ' '
                . $this->shortenTypeHint($classDefinition, $property->getPhpTypeEnum())
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
            $methods[] = $this->formatMethod($classDefinition, $method, $depth + 1);
        }

        return implode("\n\n", $methods);
    }

    private function formatMethod(ClassDefinition $classDefinition, ClassMethodDefinition $method, int $depth): string
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
            $hint = $this->shortenTypeHint($classDefinition, $parameter->getParameterType());

            $parameters[] = $hint . ' $' . $parameter->getParameterName();
        }

        $signature .= implode(', ', $parameters);

        $signature .= '): ';
        $signature .= $this->shortenTypeHint($classDefinition, $method->getReturnPhpTypeEnum());
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

    /**
     * @param ClassDefinition $classDefinition
     *
     * @return string[]
     */
    private function formatImports(ClassDefinition $classDefinition): array
    {
        $imports = [];

        foreach ($classDefinition->getImports() as $import) {
            $imports[] = 'use ' . $import->getFullyQualifiedImportableName() . ';';
        }

        return $imports;
    }

    /**
     * @param ClassDefinition $classDefinition
     * @param PhpTypeEnum $phpType
     *
     * @return string
     */
    private function shortenTypeHint(ClassDefinition $classDefinition, PhpTypeEnum $phpType): string
    {
        $typeHint = $phpType->toDeclarationType();

        if ($phpType->isObject() || $phpType->isNullableObject()) {
            if ($phpType->isNullableObject()) {
                $typeHint = ltrim($typeHint, '?');
            }

            $type = new ObjectTypeDefinition($typeHint);

            $typeHint = $type->getFullyQualifiedName();

            if (!$classDefinition->willCollideImport($type)) {
                $classDefinition->addImport($type);
                $typeHint = $type->getImportableName();
            }

            if ($phpType->isNullableObject()) {
                $typeHint = '?' . $typeHint;
            }
        }

        return $typeHint;
    }
}
