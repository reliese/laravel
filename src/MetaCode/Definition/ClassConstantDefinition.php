<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Enum\VisibilityEnum;

/**
 * Class ClassConstantDefinition
 */
class ClassConstantDefinition
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var mixed
     */
    private mixed $value;

    /**
     * @var VisibilityEnum|null
     */
    private ?VisibilityEnum $visibilityEnum;

    /**
     * ClassConstantDefinition constructor.
     *
     * @param string $name
     * @param mixed $value
     * @param VisibilityEnum|null $visibilityEnum
     */
    public function __construct(
        string $name,
        mixed $value,
        ?VisibilityEnum $visibilityEnum = null,
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->visibilityEnum = $visibilityEnum ?? VisibilityEnum::publicEnum();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return VisibilityEnum
     */
    public function getVisibilityEnum(): VisibilityEnum
    {
        return $this->visibilityEnum;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
