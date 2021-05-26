<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Enum\InstanceEnum;
use Reliese\MetaCode\Enum\PhpTypeEnum;
use Reliese\MetaCode\Enum\VisibilityEnum;
/**
 * Class ClassFieldDefinition
 */
class ClassFieldDefinition
{
    /**
     * @var PhpTypeEnum
     */
    private PhpTypeEnum $boolType;

    /**
     * @var InstanceEnum
     */
    private InstanceEnum $instanceEnum;

    /**
     * @var VisibilityEnum
     */
    private VisibilityEnum $protectedEnum;

    private string $fieldName;

    /**
     * ClassFieldDefinition constructor.
     *
     * @param string         $fieldName
     * @param PhpTypeEnum    $boolType
     * @param VisibilityEnum $protectedEnum
     * @param InstanceEnum   $instanceEnum
     */
    public function __construct(
        string $fieldName,
        PhpTypeEnum $boolType,
        VisibilityEnum $protectedEnum,
        InstanceEnum $instanceEnum
    ) {
        $this->fieldName = $fieldName;
        $this->boolType = $boolType;
        $this->protectedEnum = $protectedEnum;
        $this->instanceEnum = $instanceEnum;
    }

    /**
     * @return PhpTypeEnum
     */
    public function getBoolType(): PhpTypeEnum
    {
        return $this->boolType;
    }

    /**
     * @return InstanceEnum
     */
    public function getInstanceEnum(): InstanceEnum
    {
        return $this->instanceEnum;
    }

    /**
     * @return VisibilityEnum
     */
    public function getProtectedEnum(): VisibilityEnum
    {
        return $this->protectedEnum;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }
}