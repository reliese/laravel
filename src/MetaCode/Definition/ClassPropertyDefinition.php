<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;

/**
 * Class ClassPropertyDefinition
 */
class ClassPropertyDefinition
{
    /**
     * @return PhpTypeEnum
     */
    public function getPhpTypeEnum(): PhpTypeEnum
    {
        return $this->phpTypeEnum;
    }
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
    private ?InstanceEnum $instanceEnum;

    /**
     * @var PhpTypeEnum
     */
    private PhpTypeEnum $phpTypeEnum;

    /**
     * @var string
     */
    private string $variableName;

    /**
     * @var VisibilityEnum|null
     */
    private ?VisibilityEnum $visibilityEnum;

    /**
     * @var InstanceEnum
     */
    private InstanceEnum $setterInstanceEnum;

    /**
     * @var VisibilityEnum
     */
    private VisibilityEnum $setterVisibilityEnum;

    /**
     * ClassPropertyDefinition constructor.
     *
     * @param string $variableName
     * @param PhpTypeEnum $phpTypeEnum
     * @param VisibilityEnum|null $visibilityEnum
     * @param InstanceEnum|null $instanceEnum
     */
    public function __construct(
        string $variableName,
        PhpTypeEnum $phpTypeEnum,
        ?VisibilityEnum $visibilityEnum = null,
        ?InstanceEnum $instanceEnum = null
    ) {
        $this->variableName = $variableName;
        $this->visibilityEnum = $visibilityEnum ?? VisibilityEnum::privateEnum();
        $this->instanceEnum = $instanceEnum ?? InstanceEnum::instanceEnum();
        $this->phpTypeEnum = $phpTypeEnum;
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return $this->variableName;
    }

    /**
     * @param VisibilityEnum|null $getterVisibilityEnum
     * @param InstanceEnum|null $getterInstanceEnum
     *
     * @return $this
     */
    public function withGetter(
        ?VisibilityEnum $getterVisibilityEnum = null,
        ?InstanceEnum $getterInstanceEnum = null
    ): ClassPropertyDefinition {
        $this->getterVisibilityEnum = $getterVisibilityEnum ?? VisibilityEnum::publicEnum();
        $this->getterInstanceEnum = $getterInstanceEnum ?? InstanceEnum::instanceEnum();
        return $this;
    }

    /**
     * @return VisibilityEnum
     */
    public function getGetterVisibilityEnum(): VisibilityEnum
    {
        return $this->getterVisibilityEnum;
    }

    /**
     * @return InstanceEnum
     */
    public function getGetterInstanceEnum(): InstanceEnum
    {
        return $this->getterInstanceEnum;
    }

    /**
     * @param VisibilityEnum|null $setterVisibilityEnum
     * @param InstanceEnum|null $setterInstanceEnum
     *
     * @return $this
     */
    public function withSetter(
        ?VisibilityEnum $setterVisibilityEnum = null,
        ?InstanceEnum $setterInstanceEnum = null
    ): ClassPropertyDefinition {
        $this->setterVisibilityEnum = $setterVisibilityEnum ?? VisibilityEnum::publicEnum();
        $this->setterInstanceEnum = $setterInstanceEnum ?? InstanceEnum::instanceEnum();
        return $this;
    }

    /**
     * @return VisibilityEnum
     */
    public function getSetterVisibilityEnum(): VisibilityEnum
    {
        return $this->setterVisibilityEnum;
    }

    /**
     * @return InstanceEnum
     */
    public function getSetterInstanceEnum(): InstanceEnum
    {
        return $this->setterInstanceEnum;
    }
}
