<?php

namespace Reliese\Blueprint;

/**
 * Class UniqueKeyBlueprint
 */
class UniqueKeyBlueprint
{
    /**
     * @var ColumnOwnerInterface
     */
    private $columnOwner;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $uniqueColumnNames;

    /**
     * UniqueConstraintBlueprint constructor.
     *
     * @param ColumnOwnerInterface $columnOwner
     * @param array $uniqueColumnNames
     */
    public function __construct(
        ColumnOwnerInterface $columnOwner,
        string $name,
        array $uniqueColumnNames
    ) {
        $this->columnOwner = $columnOwner;
        $this->name = $name;
        $this->uniqueColumnNames = $uniqueColumnNames;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
