<?php

namespace Reliese\MetaCode\Definition;

use Illuminate\Support\Str;
use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;

/**
 * Class ClassPropertyDefinition
 */
class ClassPropertyDefinition
{
    /**
     * @var InstanceEnum|null
     */
    private ?InstanceEnum $getterInstanceEnum = null;

    /**
     * @var VisibilityEnum|null
     */
    private ?VisibilityEnum $getterVisibilityEnum = null;

    /**
     * @var StatementDefinitionInterface[]
     */
    private array $additionalSetterOperations = [];

    /**
     * @var InstanceEnum|null
     */
    private ?InstanceEnum $instanceEnum;

    private bool $isAfterChangeObservable = false;

    private bool $isBeforeChangeObservable = false;

    /**
     * @var PhpTypeEnum
     */
    private PhpTypeEnum $phpTypeEnum;

    /**
     * @var InstanceEnum|null
     */
    private ?InstanceEnum $setterInstanceEnum = null;

    /**
     * @var VisibilityEnum|null
     */
    private ?VisibilityEnum $setterVisibilityEnum = null;

    /**
     * @var string
     * @var mixed|null
     */
    private mixed $value = null;

    /**
     * @var string
     */
    private string $variableName;

    /**
     * @var VisibilityEnum|null
     */
    private ?VisibilityEnum $visibilityEnum;

    /**
     * ClassPropertyDefinition constructor.
     *
     * @param string              $variableName
     * @param PhpTypeEnum         $phpTypeEnum
     * @param VisibilityEnum|null $visibilityEnum
     * @param InstanceEnum|null   $instanceEnum
     */
    public function __construct(string $variableName,
        PhpTypeEnum $phpTypeEnum,
        ?VisibilityEnum $visibilityEnum = null,
        ?InstanceEnum $instanceEnum = null)
    {
        $this->variableName = $variableName;
        $this->visibilityEnum = $visibilityEnum ?? VisibilityEnum::privateEnum();
        $this->instanceEnum = $instanceEnum ?? InstanceEnum::instanceEnum();
        $this->phpTypeEnum = $phpTypeEnum;
    }

    /**
     * @return InstanceEnum
     */
    public function getGetterInstanceEnum(): InstanceEnum
    {
        return $this->getterInstanceEnum;
    }

    /**
     * @return VisibilityEnum
     */
    public function getGetterVisibilityEnum(): VisibilityEnum
    {
        return $this->getterVisibilityEnum;
    }

    /**
     * @return bool
     */
    public function getIsAfterChangeObservable(): bool
    {
        return $this->isAfterChangeObservable;
    }

    /**
     * @return bool
     */
    public function getIsBeforeChangeObservable(): bool
    {
        return $this->isBeforeChangeObservable;
    }

    /**
     * @return PhpTypeEnum
     */
    public function getPhpTypeEnum(): PhpTypeEnum
    {
        return $this->phpTypeEnum;
    }

    /**
     * @return InstanceEnum
     */
    public function getSetterInstanceEnum(): InstanceEnum
    {
        return $this->setterInstanceEnum;
    }

    /**
     * @return ClassMethodDefinition
     */
    public function getSetterMethodDefinition(ClassDefinition $containingClass): ClassMethodDefinition
    {
        $param = new FunctionParameterDefinition($this->getVariableName(), $this->getPhpTypeEnum());
        $setter = new ClassMethodDefinition(
            $this->getSetterMethodName(),
            PhpTypeEnum::staticTypeEnum(),
            [
                $param,
            ],
            $this->getSetterVisibilityEnum(),
            $this->getSetterInstanceEnum(),
        );

        if ($this->getIsBeforeChangeObservable() ) {
            $setter->appendBodyStatement(
                new RawStatementDefinition(
                    \sprintf(
                        "\$this->raiseBeforeValueChange('%s', \$this->%s, \$%s);\n",
                        $this->getVariableName(),
                        $this->getVariableName(),
                        $param->getParameterName(),
                    )
                )
            );
        }

        $setter->appendBodyStatement(new RawStatementDefinition(\sprintf("\$this->%s = $%s;\n",
                $this->getVariableName(),
                $param->getParameterName())));

        foreach ($this->additionalSetterOperations as $additionalSetterOperation) {
            $setter->appendBodyStatement($additionalSetterOperation);
        }

        if ($this->getIsAfterChangeObservable() && $containingClass->hasTrait('AfterValueChangeObservableTrait')) {
            $setter->appendBodyStatement(new RawStatementDefinition(\sprintf("\$this->raiseAfterValueChange('%s', \$this->%s);\n",
                        $this->getVariableName(),
                        $this->getVariableName())));
        }

        $setter->appendBodyStatement(new RawStatementDefinition('return $this;'));

        return $setter;
    }

    /**
     * @return VisibilityEnum
     */
    public function getSetterVisibilityEnum(): VisibilityEnum
    {
        return $this->setterVisibilityEnum;
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return $this->variableName;
    }

    /**
     * @return VisibilityEnum
     */
    public function getVisibilityEnum(): VisibilityEnum
    {
        return $this->visibilityEnum;
    }

    /**
     * @return bool
     */
    public function hasGetter(): bool
    {
        return !is_null($this->getterVisibilityEnum);
    }

    /**
     * @return bool
     */
    public function hasSetter(): bool
    {
        return !is_null($this->setterVisibilityEnum);
    }

    /**
     * @param bool $isAfterChangeObservable
     *
     * @return ClassPropertyDefinition
     */
    public function setIsAfterChangeObservable(bool $isAfterChangeObservable): ClassPropertyDefinition
    {
        $this->isAfterChangeObservable = $isAfterChangeObservable;
        return $this;
    }

    /**
     * @param bool $isBeforeChangeObservable
     *
     * @return ClassPropertyDefinition
     */
    public function setIsBeforeChangeObservable(bool $isBeforeChangeObservable): ClassPropertyDefinition
    {
        $this->isBeforeChangeObservable = $isBeforeChangeObservable;
        return $this;
    }

    /**
     * @param VisibilityEnum|null $getterVisibilityEnum
     * @param InstanceEnum|null   $getterInstanceEnum
     *
     * @return $this
     */
    public function withGetter(?VisibilityEnum $getterVisibilityEnum = null,
        ?InstanceEnum $getterInstanceEnum = null): ClassPropertyDefinition
    {
        $this->getterVisibilityEnum = $getterVisibilityEnum ?? VisibilityEnum::publicEnum();
        $this->getterInstanceEnum = $getterInstanceEnum ?? InstanceEnum::instanceEnum();
        return $this;
    }

    /**
     * @param VisibilityEnum|null $setterVisibilityEnum
     * @param InstanceEnum|null   $setterInstanceEnum
     *
     * @return $this
     */
    public function withSetter(?VisibilityEnum $setterVisibilityEnum = null,
        ?InstanceEnum $setterInstanceEnum = null): ClassPropertyDefinition
    {
        $this->setterVisibilityEnum = $setterVisibilityEnum ?? VisibilityEnum::publicEnum();
        $this->setterInstanceEnum = $setterInstanceEnum ?? InstanceEnum::instanceEnum();
        return $this;
    }

    public function getSetterMethodName(): string
    {
        return 'set' . Str::studly($this->getVariableName());
    }

    /**
     * @return bool
     */
    public function hasValue(): bool
    {
        return !empty($this->getValue());
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param StatementDefinitionInterface $statementDefinition
     *
     * @return $this
     */
    public function addAdditionalSetterOperation(StatementDefinitionInterface $statementDefinition): static
    {
        $this->additionalSetterOperations[] = $statementDefinition;
        return $this;
    }
}
