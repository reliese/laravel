<?php

namespace Reliese\Blueprint;

/**
 * Class IndexBlueprint
 */
class IndexBlueprint
{
    /**
     * @var array
     */
    private $columnNames;

    /**
     * @var string
     */
    private $indexName;

    /**
     * @var ColumnOwnerInterface
     */
    private $owner;

    /**
     * IndexBlueprint constructor.
     *
     * @param ColumnOwnerInterface $owner
     * @param string $indexName
     * @param array $columnNames
     */
    public function __construct(
        ColumnOwnerInterface $owner,
        string $indexName,
        array $columnNames
    ) {
        $this->owner = $owner;
        $this->indexName = $indexName;
        $this->columnNames = $columnNames;
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return $this->indexName;
    }
}
