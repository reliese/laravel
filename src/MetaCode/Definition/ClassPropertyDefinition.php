<?php

namespace Reliese\MetaCode\Definition;

/**
 * Class ClassPropertyDefinition
 */
class ClassPropertyDefinition
{
    /**
     * @var VisibilityEnum
     */
    private ?VisibilityEnum $getterVisibilityEnum = null;

    /**
     * @var InstanceEnum
     */
    private ?InstanceEnum $getterInstanceEnum = null;

    /**
     * @var InstanceEnum|null
     */
    private ?InstanceEnum $memberInstanceEnum;

    private string $memberVariableName;

    /**
     * @var VisibilityEnum|null
     */
    private ?VisibilityEnum $memberVisibilityEnum;

    /**
     * @var InstanceEnum
     */
    private InstanceEnum $setterInstanceEnum;

    /**
     * @var VisibilityEnum
     */
    private VisibilityEnum $setterVisibilityEnum;

    public function __construct(string $memberVariableName,
        ?VisibilityEnum $memberVisibilityEnum = null,
        ?InstanceEnum $memberInstanceEnum = null
    ) {
        $this->memberVariableName = $memberVariableName;
        $this->memberVisibilityEnum = $memberVisibilityEnum ?? VisibilityEnum::privateEnum();
        $this->memberInstanceEnum = $memberInstanceEnum ?? InstanceEnum::instanceEnum();
    }

    public function getMemberVariableName()
    {
        return $this->memberVariableName;
    }

    public function withGetter(
        ?VisibilityEnum $getterVisibilityEnum = null,
        ?InstanceEnum $getterInstanceEnum = null
    ): static {
        $this->getterVisibilityEnum = $getterVisibilityEnum ?? VisibilityEnum::publicEnum();
        $this->getterInstanceEnum = $getterInstanceEnum ?? InstanceEnum::instanceEnum();
        return $this;
    }

    public function getGetterVisibilityEnum(): VisibilityEnum
    {
        return $this->getterVisibilityEnum;
    }
    public function getGetterInstanceEnum(): InstanceEnum
    {
        return $this->getterInstanceEnum;
    }

    public function withSetter(
        ?VisibilityEnum $setterVisibilityEnum = null,
        ?InstanceEnum $setterInstanceEnum = null
    ): static {
        $this->setterVisibilityEnum = $setterVisibilityEnum ?? VisibilityEnum::publicEnum();
        $this->setterInstanceEnum = $setterInstanceEnum ?? InstanceEnum::instanceEnum();
        return $this;
    }

    public function getSetterVisibilityEnum(): VisibilityEnum
    {
        return $this->setterVisibilityEnum;
    }
    public function getSetterInstanceEnum(): InstanceEnum
    {
        return $this->setterInstanceEnum;
    }
}
