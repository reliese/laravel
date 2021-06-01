<?php

namespace Reliese\MetaCode\Format;

use Illuminate\Support\Str;
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
    /**
     * @var IndentationProvider|null
     */
    private ?IndentationProvider $indentationProvider;

    public function __construct(IndentationProvider $indentationProvider) {
        $this->indentationProvider = $indentationProvider;
    }

    /**
     * @param ClassDefinition $classDefinition
     *
     * @return string
     */
    public function format(ClassDefinition $classDefinition): string
    {
        $lines = [];

        $this->prepareGettersAndSetters($classDefinition);

        $parent = $this->formatParentClass($classDefinition);

        $body = [];

        $body[] = $this->formatTraits($classDefinition);
        $body[] = $this->formatConstants($classDefinition);
        $body[] = $this->formatProperties($classDefinition);
        $body[] = $this->formatConstructor($classDefinition);
        $body[] = $this->formatMethods($classDefinition);

        $lines[] = 'namespace ' . $classDefinition->getClassNamespace() . ";\n\n";

        $imports = $this->formatImports($classDefinition);

        if (!empty($imports)) {
            $lines[] = implode("\n", $imports) . "\n\n";
        }

        $lines[] = "/**\n";
        $lines[] = ' * ' . Str::studly($classDefinition->getStructureType()) . ' ' . $classDefinition->getName() . "\n";
        $lines[] = " * \n";
        $lines[] = " * Created by Reliese\n";
        foreach ($classDefinition->getClassComments() as $line) {
            $lines[] = " * \n * ".$line."\n";
        }
        $lines[] = " */\n";
        $lines[] = $classDefinition->getAbstractEnumType()->toReservedWord(true)
            . Str::lower($classDefinition->getStructureType())
            . ' '
            . $classDefinition->getClassName();

        if (!empty($parent)) {
            $lines[] = ' extends ' . $parent;
        }

        if ($classDefinition->hasInterfaces()) {
            $lines[] = ' implements '.\implode(', ', $classDefinition->getInterfaces());
        }

        $lines[] = "\n";
        $lines[] = "{\n";

        // Filter away empty blocks and space them with one empty line
        $lines[] = implode("\n\n", array_filter($body));
        $lines[] = "\n}\n";

        return implode('', array_filter($lines));
    }

    /**
     * @param ClassDefinition $classDefinition
     */
    private function prepareGettersAndSetters(ClassDefinition $classDefinition): void
    {
        /** @var ClassPropertyDefinition $property */
        foreach ($classDefinition->getProperties() as $property) {
            if ($property->hasSetter()) {
                $classDefinition->addMethodDefinition(
                    $property->getSetterMethodDefinition($classDefinition)
                );
            }
            if ($property->hasGetter()) {
                $classDefinition->addMethodDefinition(
                    $property->getGetterMethodDefinition($classDefinition)
                );
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
        if (!$classDefinition->hasParentClass()) {
            return '';
        }

        return $classDefinition->getParentObjectTypeDefinition()->getImportableName();
    }

    /**
     * @param ClassDefinition $classDefinition
     *
     * @return string
     */
    private function formatTraits(ClassDefinition $classDefinition): string
    {
        $traits = [];

        foreach ($classDefinition->getTraits() as $trait) {
            $traits[] = rtrim($trait->toPhpCode($this->indentationProvider->increment()));
        }

        return implode("\n", $traits);
    }

//    private function formatTrait(ClassDefinition $classDefinition, ClassTraitDefinition $trait, int $depth): string
//    {
//        $object = $trait->getFullyQualifiedName();
//
//        if (!$classDefinition->willCollideImport($trait)) {
//            $classDefinition->addImport($trait);
//            $object = $trait->getTraitName();
//        }
//
//
//        return $this->indentationProvider->getIndentation($depth) . 'use ' . $object . ';';
//    }

    /**
     * @param ClassDefinition $classDefinition
     *
     * @return string
     */
    private function formatConstants(ClassDefinition $classDefinition): string
    {
        $constants = [];

        foreach ($classDefinition->getConstants() as $constant) {
            $constants[] = rtrim($constant->toPhpCode($this->indentationProvider->increment()));
        }

        return implode("\n", $constants);
    }

    /**
     * @param ClassDefinition $classDefinition
     *
     * @return string
     */
    private function formatProperties(ClassDefinition $classDefinition): string
    {
        $properties = [];

        foreach ($classDefinition->getProperties() as $property) {
            $properties[] = rtrim($property->toPhpCode($this->indentationProvider->increment()));
        }

        return implode("\n", $properties);
    }

//    private function formatProperty(ClassDefinition $classDefinition, ClassPropertyDefinition $property, int $depth): string
//    {
//        $defaultValueString = '';
//        if ($property->hasDefaultValueStatement()) {
//            $defaultValueString = ' = '. $property->getDefaultValueStatement()->toPhpCode(IndentationProvider::NoIndentation());
//        } elseif ($property->getPhpTypeEnum()->isNullable()) {
//            $defaultValueString = ' = null';
//        }
//
//        return $this->indentationProvider->getIndentation()
//                . $property->getVisibilityEnum()->toReservedWord()
//                . ' '
//                . static::shortenTypeHint($classDefinition, $property->getPhpTypeEnum())
//                . ' $'
//                . $property->getVariableName()
//                . $defaultValueString
//                . ';';
//    }

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
                $param,
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
     * @param ClassDefinition $classDefinition
     *
     * @return string
     */
    private function formatMethods(ClassDefinition $classDefinition): string
    {
        $methods = [];

        foreach ($classDefinition->getMethods() as $method) {
            $methods[] = $method->toPhpCode($this->indentationProvider->increment());
        }

        return implode("\n\n", $methods);
    }

//    private function formatMethod(ClassDefinition $classDefinition, ClassMethodDefinition $method): string
//    {
//        $indentationProvider = $this->indentationProvider->increment();
//        $signature = $indentationProvider->getIndentation();
//
//        if ($method->getVisibilityEnum()) {
//            $signature .= $method->getVisibilityEnum()->toReservedWord() . ' ';
//        }
//
//        if ($method->getAbstractEnum()->isAbstract()) {
//            $signature .= $method->getAbstractEnum()->toReservedWord() . ' ';
//        }
//
//        $signature .= 'function ' . $method->getFunctionName() . '(';
//
//        $parameters = [];
//        /** @var FunctionParameterDefinition $parameter */
//        foreach ($method->getFunctionParameterDefinitions() as $parameter) {
//            $hint = static::shortenTypeHint($classDefinition, $parameter->getParameterType());
//
//            $parameterPhpCode = $hint . ' ';
//            if ($parameter->isOutputParameter()) {
//                $parameterPhpCode .= '&';
//            }
//            $parameterPhpCode .= '$' . $parameter->getParameterName();
//            if ($parameter->hasDefaultValueStatementDefinition()) {
//                $parameterPhpCode .= ' = '.$parameter->getDefaultValueStatementDefinition()
//                        ->toPhpCode(IndentationProvider::NoIndentation());
//            }
//            $parameters[] = $parameterPhpCode;
//        }
//
//        $signature .= implode(', ', $parameters);
//
//        $signature .= ')';
//        if ($method->getReturnPhpTypeEnum()->isDefined()) {
//            /*
//             * This condition is required because constructors do not have return types
//             */
//            $signature .= ": ". static::shortenTypeHint($classDefinition, $method->getReturnPhpTypeEnum());
//        }
//        if ($method->getAbstractEnum()->isAbstract()) {
//            return $signature . ";\n";
//        }
//        $signature .= "\n";
//
//        $signature .= $indentationProvider->getIndentation() . "{\n";
//
//        foreach ($method->getBlockStatements() as $statement) {
//            $signature .= $statement->toPhpCode($indentationProvider->increment())
//                . "\n";
//        }
//
//
//        $signature .= $indentationProvider->getIndentation() . '}';
//
//        return $signature;
//    }

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
    private static function shortenTypeHint(ClassDefinition $classDefinition, PhpTypeEnum $phpType): string
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

    private function formatConstructor(ClassDefinition $classDefinition): string
    {
        if (!$classDefinition->getConstructorStatementsCollection()->hasStatements()) {
            return "";
        }

        $constructorMethodDefinition = new ClassMethodDefinition('__construct', PhpTypeEnum::notDefined());
        $constructorMethodDefinition->appendBodyStatement($classDefinition->getConstructorStatementsCollection());

        return $constructorMethodDefinition->toPhpCode($this->indentationProvider->increment());
    }
}
