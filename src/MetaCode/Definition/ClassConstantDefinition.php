<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
use Reliese\MetaCode\Format\IndentationProviderInterface;
use function is_float;
use function is_int;
use function sprintf;
use function var_export;

/**
 * Class ClassConstantDefinition
 */
class ClassConstantDefinition implements StatementDefinitionInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var PhpTypeEnum
     */
    private PhpTypeEnum $phpTypeEnum;

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
     * @param string              $name
     * @param mixed               $value
     * @param VisibilityEnum|null $visibilityEnum
     * @param PhpTypeEnum|null    $phpTypeEnum
     */
    public function __construct(
        string $name,
        mixed $value,
        ?VisibilityEnum $visibilityEnum = null,
        ?PhpTypeEnum $phpTypeEnum = null,
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->visibilityEnum = $visibilityEnum ?? VisibilityEnum::publicEnum();
        $this->phpTypeEnum = $phpTypeEnum ?? PhpTypeEnum::stringType();
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

    public function toSelfReference(): string
    {
        return 'static::'.$this->getName();
    }

    public function toPhpCode(IndentationProviderInterface $indentationProvider, int $blockDepth): string
    {
        $statement = $indentationProvider->getIndentation($blockDepth).$this->getVisibilityEnum()->toReservedWord()
        . ' const '
        . $this->getName()
        . ' = ';

        if ($this->phpTypeEnum->isNumeric()) {
            $statement .= $this->phpTypeEnum->toNumericCast($this->getValue());
        } else {
            $statement .= var_export($this->getValue(), true);
        }

        return $statement;
    }

    /**
     * @return PhpTypeEnum
     */
    public function getPhpTypeEnum(): PhpTypeEnum
    {
        return $this->phpTypeEnum;
    }
}
