<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Enum\PhpTypeEnum;

/**
 * Class FunctionParameterDefinition
 */
class FunctionParameterDefinition
{
    /**
     * @var bool
     */
    private bool $isOutputParameter;

    /**
     * @var string
     */
    private string $parameterName;

    /**
     * @var PhpTypeEnum
     */
    private PhpTypeEnum $parameterType;

    /**
     * FunctionParameterDefinition constructor.
     *
     * @param string $parameterName
     * @param PhpTypeEnum $parameterType
     * @param bool $isOutputParameter
     */
    public function __construct(
        string $parameterName,
        PhpTypeEnum $parameterType,
        bool $isOutputParameter = false
    ) {
        $this->parameterName = $parameterName;
        $this->parameterType = $parameterType;
        $this->isOutputParameter = $isOutputParameter;
    }

    /**
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * @return PhpTypeEnum
     */
    public function getParameterType(): PhpTypeEnum
    {
        return $this->parameterType;
    }

    /**
     * @return bool
     */
    public function isOutputParameter(): bool
    {
        return $this->isOutputParameter;
    }
}
