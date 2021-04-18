<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Enum\AbstractEnum;
use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;

/**
 * Class ClassMethodDefinition
 */
class ClassMethodDefinition extends FunctionDefinition
{
    /**
     * @var AbstractEnum|null
     */
    private ?AbstractEnum $abstractEnum;

    /**
     * @var InstanceEnum|null
     */
    private ?InstanceEnum $instanceEnum;

    /**
     * ClassMethodDefinition constructor.
     *
     * @param string $functionName
     * @param PhpTypeEnum $returnType
     * @param FunctionParameterDefinition[] $functionParameterDefinitions
     * @param VisibilityEnum|null $visibilityEnum
     * @param InstanceEnum|null $instanceEnum
     * @param AbstractEnum|null $abstractEnum
     */
    public function __construct(
        string $functionName,
        PhpTypeEnum $returnType,
        array $functionParameterDefinitions = [],
        ?VisibilityEnum $visibilityEnum = null,
        ?InstanceEnum $instanceEnum = null,
        ?AbstractEnum $abstractEnum = null
    ) {
        parent::__construct($functionName, $returnType, $functionParameterDefinitions, $visibilityEnum);
        $this->instanceEnum = $instanceEnum ?? InstanceEnum::instanceEnum();
        $this->abstractEnum = $abstractEnum ?? AbstractEnum::concreteEnum();
    }

    /**
     * @return AbstractEnum
     */
    public function getAbstractEnum(): AbstractEnum
    {
        return $this->abstractEnum;
    }

    /**
     * @return InstanceEnum
     */
    public function getInstanceEnum(): InstanceEnum
    {
        return $this->instanceEnum;
    }

}
