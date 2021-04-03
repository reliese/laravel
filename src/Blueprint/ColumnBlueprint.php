<?php

namespace Reliese\Blueprint;

use Doctrine\DBAL\Schema\Column;
/**
 * Class ColumnBlueprint
 */
class ColumnBlueprint
{
    /**
     * @var string
     */
    private $columnName;

    /**
     * @var ColumnOwnerInterface
     */
    private $columnOwner;

    /**
     * @var string
     */
    private $dataType;

    /**
     * @var bool
     */
    private $hasDefault;

    /**
     * @var bool
     */
    private $isAutoincrement;

    /**
     * @var bool
     */
    private $isNullable;

    /**
     * @var int
     */
    private $maximumCharacters;

    /**
     * @var int
     */
    private $numericPrecision;

    /**
     * @var int
     */
    private $numericScale;

    /**
     * ColumnBlueprint constructor.
     *
     * @param ColumnOwnerInterface $tableBlueprint
     * @param string $columnName
     */

    /**
     * ColumnBlueprint constructor.
     *
     * @param ColumnOwnerInterface $columnOwner The Table or View that owns the column
     * @param string $columnName
     * @param string $dataType
     * @param bool $isNullable
     * @param int $maximumCharacters
     * @param int $numericPrecision
     * @param int $numericScale
     * @param bool $isAutoincrement
     * @param bool $hasDefault
     */
    public function __construct(ColumnOwnerInterface $columnOwner,
        string $columnName,
        string $dataType,
        bool $isNullable,
        int $maximumCharacters,
        int $numericPrecision,
        int $numericScale,
        bool $isAutoincrement,
        bool $hasDefault)
    {
        $this->columnOwner = $columnOwner;
        $this->setColumnName($columnName);
        $this->setDataType($dataType);
        $this->setIsNullable($isNullable);
        $this->setMaximumCharacters($maximumCharacters);
        $this->setNumericPrecision($numericPrecision);
        $this->setNumericScale($numericScale);
        $this->setIsAutoincrement($isAutoincrement);
        $this->setHasDefault($hasDefault);
    }

    /**
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * @return bool
     */
    public function getHasDefault(): bool
    {
        return $this->hasDefault;
    }

    /**
     * @return bool
     */
    public function getIsAutoincrement(): bool
    {
        return $this->isAutoincrement;
    }

    /**
     * @return bool
     */
    public function getIsNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * @return int
     */
    public function getMaximumCharacters(): int
    {
        return $this->maximumCharacters;
    }

    /**
     * @return int
     */
    public function getNumericPrecision(): int
    {
        return $this->numericPrecision;
    }

    /**
     * @return int
     */
    public function getNumericScale(): int
    {
        return $this->numericScale;
    }

    /**
     * @return string
     */
    public function getUniqueName(): string
    {
        return sprintf('%s.%s',
            $this->getOwner()->getUniqueName(),
            $this->getColumnName()
        );
    }

    /**
     * @return ColumnOwnerInterface
     */
    public function getOwner(): ColumnOwnerInterface
    {
        return $this->columnOwner;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setColumnName(string $value): self
    {
        $this->columnName = $value;
        return $this;
    }

    /**
     * The normalized data type which was derived from the raw data type in the database.
     * For example... 'unsigned bigint' should be 'float'
     *
     * @param string $value
     *
     * @return $this
     */
    public function setDataType(string $value): self
    {
        /**
         * TODO: Validate that $value matches a valid Laravel Model Field data type.
         */

        $this->dataType = $value;
        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setHasDefault(bool $value): self
    {
        $this->hasDefault = $value;
        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsAutoincrement(bool $value): self
    {
        $this->isAutoincrement = $value;
        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsNullable(bool $value): self
    {
        $this->isNullable = $value;
        return $this;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMaximumCharacters(int $value): self
    {
        $this->maximumCharacters = $value;
        return $this;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setNumericPrecision(int $value): self
    {
        $this->numericPrecision = $value;
        return $this;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setNumericScale(int $value): self
    {
        $this->numericScale = $value;
        return $this;
    }
}
