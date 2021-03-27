<?php

namespace Reliese\Blueprint;

use Reliese\Meta\Blueprint as DeprecatedTableBlueprint;

/**
 * Class TableBlueprint
 */
class TableBlueprint
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var SchemaBlueprint
     */
    private $schemaBlueprint;

    /**
     * @var DeprecatedTableBlueprint
     */
    private $deprecatedTableBlueprint;

    /**
     * TableBlueprint constructor.
     * @param SchemaBlueprint $schemaBlueprint
     * @param $tableName
     * @param DeprecatedTableBlueprint $deprecatedTableBlueprint
     */
    public function __construct(
        SchemaBlueprint $schemaBlueprint,
        string $tableName,
        DeprecatedTableBlueprint $deprecatedTableBlueprint
    ) {
        $this->tableName = $tableName;
        $this->schemaBlueprint = $schemaBlueprint;
        $this->deprecatedTableBlueprint = $deprecatedTableBlueprint;
    }
}