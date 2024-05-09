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
     * @param $tableName
     * @param SchemaBlueprint $schemaBlueprint
     */
    public function __construct(
        SchemaBlueprint $schemaBlueprint,
        $tableName,
        DeprecatedTableBlueprint $depricatedTableBlueprint
    ) {
        $this->tableName = $tableName;
        $this->schemaBlueprint = $schemaBlueprint;
        $this->deprecatedTableBlueprint = $depricatedTableBlueprint;
    }
}