<?php

namespace Reliese\Generator\DataAttribute;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Configuration\DataAttributeGeneratorConfiguration;
use Reliese\Generator\MySqlDataTypeMap;
use Reliese\MetaCode\Definition\TraitDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Tool\ClassNameTool;
use const DIRECTORY_SEPARATOR;

/**
 * Class DataAttributeGenerator
 */
class DataAttributeGenerator
{
    /**
     * @var DataAttributeGeneratorConfiguration
     */
    private DataAttributeGeneratorConfiguration $dataAttributeGeneratorConfiguration;

    /**
     * @var MySqlDataTypeMap
     */
    private MySqlDataTypeMap $dataTypeMap;

    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    /**
     * DataAttributeGenerator constructor.
     *
     * @param DataAttributeGeneratorConfiguration $dataAttributeGeneratorConfiguration
     */
    public function __construct(
        DataAttributeGeneratorConfiguration $dataAttributeGeneratorConfiguration
    ) {
        $this->dataAttributeGeneratorConfiguration = $dataAttributeGeneratorConfiguration;
        /*
         * TODO: inject a MySql / Postgress or other DataType mapping as needed
         */
        $this->dataTypeMap = new MySqlDataTypeMap();
    }

    /**
     * @param TableBlueprint $tableBlueprint
     */
    public function fromColumnBlueprint(TableBlueprint $tableBlueprint)
    {
        foreach ($tableBlueprint->getColumnBlueprints() as $columnBlueprint) {

            if ($this->isExcluded($tableBlueprint, $columnBlueprint)) {
                Log::warning("Skipping column: " . $tableBlueprint->getName() . '.' . $columnBlueprint->getColumnName());
                continue;
            }

            $traitName = $this->getTraitName($tableBlueprint, $columnBlueprint);

            $namespace = $this->getTraitNamespace($tableBlueprint, $columnBlueprint);

            $traitDefinition = new TraitDefinition($traitName, $namespace);

            $traitDefinition
                ->addClassComment(
                    sprintf("Generated from table column %s.%s", $tableBlueprint->getName(), $columnBlueprint->getColumnName())
                )
                ->addClassComment(
                "This file is only generated if it does not already exist. To regenerate, remove this file."
                )
            ;

            $propertyName = ClassNameTool::columnNameToPropertyName($columnBlueprint->getColumnName());

            $phpTypeEnum = $this->dataTypeMap->getPhpTypeEnumFromDatabaseType(
                $columnBlueprint->getDataType(),
                $columnBlueprint->getMaximumCharacters(),
                $columnBlueprint->getNumericPrecision(),
                $columnBlueprint->getNumericScale(),
                $columnBlueprint->getIsNullable()
            );

            $traitDefinition->addProperty(
                (new ClassPropertyDefinition($propertyName, $phpTypeEnum))
                ->withSetter()
                ->withGetter()
            );

            /*
             * Write the Class Files
             */
            $this->writeTraitFile($traitDefinition);
        }
    }

    /**
     * @param TableBlueprint $tableBlueprint
     * @param ColumnBlueprint $columnBlueprint
     *
     * @return string
     */
    public function getFullyQualifiedTraitName(TableBlueprint $tableBlueprint, ColumnBlueprint $columnBlueprint): string
    {
        return $this->getTraitNamespace($tableBlueprint, $columnBlueprint).'\\'.$this->getTraitName($tableBlueprint, $columnBlueprint);
    }

    public function getTraitNamespace(TableBlueprint $tableBlueprint, ColumnBlueprint $columnBlueprint): string
    {
        $tableClassName = ClassNameTool::snakeCaseToClassName(null, $tableBlueprint->getName(), null);
        return $this->dataAttributeGeneratorConfiguration->getNamespace().'\\'.$tableClassName;
    }

    public function getTraitName(
        TableBlueprint $tableBlueprint,
        ColumnBlueprint $columnBlueprint
    ): string {
        return ClassNameTool::snakeCaseToClassName(
            $this->dataAttributeGeneratorConfiguration->getTraitPrefix(),
            $columnBlueprint->getColumnName(),
            $this->dataAttributeGeneratorConfiguration->getTraitSuffix(),
        );
    }

    /**
     * @param TableBlueprint $tableBlueprint
     * @param ColumnBlueprint $columnBlueprint
     *
     * @return bool
     */
    private function isExcluded(TableBlueprint $tableBlueprint, ColumnBlueprint $columnBlueprint)
    {
        if ('id' === $columnBlueprint->getColumnName()) {
            return true;
        }
        return false;
    }

    /**
     * @param TraitDefinition $traitDefinition
     */
    private function writeTraitFile(
        TraitDefinition $traitDefinition
    ): void
    {
        $classFormatter = new ClassFormatter();

        $traitPhpCode = $classFormatter->format($traitDefinition);
//        echo "\n---Trait---\n$traitPhpCode\n\n";

        $traitDirectory = $this->dataAttributeGeneratorConfiguration->getPath();
//        echo "\n---Trait Path---\n$traitDirectory\n\n";

        if (!is_dir($traitDirectory)) {
            \mkdir($traitDirectory, 0755, true);
        }

        $traitFilePath = $traitDirectory . DIRECTORY_SEPARATOR . $traitDefinition->getName() . '.php';
//        echo "\n---Trait File---\n$traitFilePath\n\n";
        if (!\file_exists($traitFilePath)) {
            \file_put_contents($traitFilePath, $traitPhpCode);
        } else {
            Log::warning("File already exists, skipped: $traitFilePath");
        }
    }
}
