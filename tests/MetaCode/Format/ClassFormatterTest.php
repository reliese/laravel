<?php

namespace MetaCode\Format;

use Reliese\MetaCode\Definition\ClassConstantDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\FunctionParameterDefinition;
use Reliese\MetaCode\Definition\RawStatementDefinition;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Format\ClassFormatter;
use TestCase;

/**
 * Class ClassFormatterTest
 */
class ClassFormatterTest extends TestCase
{
    /**
     * @test
     */
    public function it_formats_an_empty_class_with_namespace()
    {
        $expectedClassOutput =
<<<PHP
<?php

namespace OneNamespace;

/**
 * Class OneClass
 * 
 * Created by Reliese
 */
class OneClass
{
}

PHP;

        $classDefinition = new ClassDefinition('OneClass', '\OneNamespace');

        $classFormatter = new ClassFormatter();

        $classOutput = $classFormatter->format($classDefinition);

        $this->assertEquals($expectedClassOutput, $classOutput);
    }

    /**
     * @test
     */
    public function it_formats_a_class_with_one_parameter()
    {
        $expectedClassOutput =
<<<PHP
<?php

namespace OneNamespace;

/**
 * Class OneClass
 * 
 * Created by Reliese
 */
class OneClass
{
    private string \$aProperty;
}

PHP;

        $aProperty = new ClassPropertyDefinition('aProperty', PhpTypeEnum::stringType());

        $classDefinition = new ClassDefinition('OneClass', '\OneNamespace');
        $classDefinition->addProperty($aProperty);

        $classFormatter = new ClassFormatter();

        $classOutput = $classFormatter->format($classDefinition);

        $this->assertEquals($expectedClassOutput, $classOutput);
    }

    /**
     * @test
     */
    public function it_formats_a_class_with_two_parameters()
    {
        $expectedClassOutput =
<<<PHP
<?php

namespace OneNamespace;

/**
 * Class OneClass
 * 
 * Created by Reliese
 */
class OneClass
{
    private string \$aProperty;
    private string \$anotherProperty;
}

PHP;

        $aProperty = new ClassPropertyDefinition('aProperty', PhpTypeEnum::stringType());
        $anotherProperty = new ClassPropertyDefinition('anotherProperty', PhpTypeEnum::stringType());

        $classDefinition = new ClassDefinition('OneClass', '\OneNamespace');
        $classDefinition->addProperty($aProperty);
        $classDefinition->addProperty($anotherProperty);

        $classFormatter = new ClassFormatter();

        $classOutput = $classFormatter->format($classDefinition);

        $this->assertEquals($expectedClassOutput, $classOutput);
    }

    /**
     * @test
     */
    public function it_formats_a_class_with_one_constant()
    {
        $expectedClassOutput =
<<<PHP
<?php

namespace OneNamespace;

/**
 * Class OneClass
 * 
 * Created by Reliese
 */
class OneClass
{
    public const ONE_CONSTANT = 'SomeValue';
}

PHP;

        $oneConstant = new ClassConstantDefinition('ONE_CONSTANT', 'SomeValue');

        $classDefinition = new ClassDefinition('OneClass', '\OneNamespace');
        $classDefinition->addConstant($oneConstant);

        $classFormatter = new ClassFormatter();

        $classOutput = $classFormatter->format($classDefinition);

        $this->assertEquals($expectedClassOutput, $classOutput);
    }

    /**
     * @test
     */
    public function it_formats_a_class_with_two_constants()
    {
        $expectedClassOutput =
<<<PHP
<?php

namespace OneNamespace;

/**
 * Class OneClass
 * 
 * Created by Reliese
 */
class OneClass
{
    public const ONE_CONSTANT = 'SomeValue';
    public const ANOTHER_CONSTANT = 'AnotherValue';
}

PHP;

        $oneConstant = new ClassConstantDefinition('ONE_CONSTANT', 'SomeValue');
        $anotherConstant = new ClassConstantDefinition('ANOTHER_CONSTANT', 'AnotherValue');

        $classDefinition = new ClassDefinition('OneClass', '\OneNamespace');
        $classDefinition->addConstant($oneConstant)
                        ->addConstant($anotherConstant);

        $classFormatter = new ClassFormatter();

        $classOutput = $classFormatter->format($classDefinition);

        $this->assertEquals($expectedClassOutput, $classOutput);
    }

    /**
     * @test
     */
    public function it_formats_a_class_with_two_constants_and_two_properties()
    {
        $expectedClassOutput =
<<<PHP
<?php

namespace OneNamespace;

/**
 * Class OneClass
 * 
 * Created by Reliese
 */
class OneClass
{
    public const ONE_CONSTANT = 'SomeValue';
    public const ANOTHER_CONSTANT = 'AnotherValue';

    private string \$aProperty;
    private string \$anotherProperty;
}

PHP;

        $oneConstant = new ClassConstantDefinition('ONE_CONSTANT', 'SomeValue');
        $anotherConstant = new ClassConstantDefinition('ANOTHER_CONSTANT', 'AnotherValue');
        $aProperty = new ClassPropertyDefinition('aProperty', PhpTypeEnum::stringType());
        $anotherProperty = new ClassPropertyDefinition('anotherProperty', PhpTypeEnum::stringType());

        $classDefinition = new ClassDefinition('OneClass', '\OneNamespace');
        $classDefinition->addConstant($oneConstant)
                        ->addConstant($anotherConstant)
                        ->addProperty($aProperty)
                        ->addProperty($anotherProperty);

        $classFormatter = new ClassFormatter();

        $classOutput = $classFormatter->format($classDefinition);

        $this->assertEquals($expectedClassOutput, $classOutput);
    }

    /**
     * @test
     */
    public function it_formats_a_class_with_a_method()
    {
        $expectedClassOutput =
<<<PHP
<?php

namespace OneNamespace;

/**
 * Class OneClass
 * 
 * Created by Reliese
 */
class OneClass
{
    public function aMethod(string \$aParameter): string
    {
        return \$aParameter;
    }
}

PHP;

        $aParameter = new FunctionParameterDefinition(
            'aParameter',
            PhpTypeEnum::stringType()
        );

        $aMethod = new ClassMethodDefinition(
            'aMethod',
            PhpTypeEnum::stringType(),
            [
                $aParameter,
            ]
        );

        $aMethod->appendBodyStatement(new RawStatementDefinition('return $aParameter;'));

        $classDefinition = new ClassDefinition('OneClass', '\OneNamespace');
        $classDefinition->addMethodDefinition($aMethod);

        $classFormatter = new ClassFormatter();

        $classOutput = $classFormatter->format($classDefinition);

        $this->assertEquals($expectedClassOutput, $classOutput);
    }

    /**
     * @test
     */
    public function it_formats_a_class_with_a_method_and_two_params()
    {
        $expectedClassOutput =
<<<PHP
<?php

namespace OneNamespace;

/**
 * Class OneClass
 * 
 * Created by Reliese
 */
class OneClass
{
    public function aMethod(string \$aParameter, \OneNamespace\OneClass \$anotherParameter): \OneNamespace\OneClass
    {
        return \$anotherParameter;
    }
}

PHP;

        $aParameter = new FunctionParameterDefinition(
            'aParameter',
            PhpTypeEnum::stringType()
        );

        $anotherParameter = new FunctionParameterDefinition(
            'anotherParameter',
            PhpTypeEnum::objectType('\OneNamespace\OneClass')
        );

        $aMethod = new ClassMethodDefinition(
            'aMethod',
            PhpTypeEnum::objectType('\OneNamespace\OneClass'),
            [
                $aParameter,
                $anotherParameter
            ]
        );

        $aMethod->appendBodyStatement(new RawStatementDefinition('return $anotherParameter;'));

        $classDefinition = new ClassDefinition('OneClass', '\OneNamespace');
        $classDefinition->addMethodDefinition($aMethod);

        $classFormatter = new ClassFormatter();

        $classOutput = $classFormatter->format($classDefinition);

        $this->assertEquals($expectedClassOutput, $classOutput);
    }

    /**
     * @test
     */
    public function it_formats_a_class_with_property_and_getters_setters()
    {
        $expectedClassOutput =
<<<PHP
<?php

namespace OneNamespace;

/**
 * Class OneClass
 * 
 * Created by Reliese
 */
class OneClass
{
    private string \$oneProperty;

    public function setOneProperty(string \$oneProperty): static
    {
        \$this->oneProperty = \$oneProperty;

        return \$this;
    }

    public function getOneProperty(): string
    {
        return \$this->oneProperty;
    }
}

PHP;

        $oneProperty = new ClassPropertyDefinition('oneProperty', PhpTypeEnum::stringType());
        $oneProperty
            ->withGetter()
            ->withSetter();

        $classDefinition = new ClassDefinition('OneClass', '\OneNamespace');
        $classDefinition->addProperty($oneProperty);

        $classFormatter = new ClassFormatter();

        $classOutput = $classFormatter->format($classDefinition);

        $this->assertEquals($expectedClassOutput, $classOutput);
    }
}
