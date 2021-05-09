<?php

namespace Reliese\Blueprint;

/**
 * Trait SchemaMemberTrait
 *
 * Generic implementation of the SchemaMemberInterface
 *
 * @package Reliese\Blueprint
 */
trait SchemaMemberTrait
{
    /**
     * @var SchemaBlueprint
     */
    private SchemaBlueprint $schemaBlueprint;

    /**
     * @var string
     */
    private string $name;

    /**
     * @return SchemaBlueprint
     */
    public function getSchemaBlueprint(): SchemaBlueprint
    {
        return $this->schemaBlueprint;
    }

    /**
     * @param SchemaBlueprint $schemaBlueprint
     */
    public function setSchemaBlueprint(SchemaBlueprint $schemaBlueprint)
    {
        $this->schemaBlueprint = $schemaBlueprint;
    }

    /**
     * Returns the name of that uniquely identifies this schema member
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
}