<?php

namespace Reliese\Analyser\Doctrine;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\ConnectionInterface;
use Reliese\Configuration\RelieseConfiguration;

/**
 * Interface DoctrineDatabaseAssistantInterface
 */
interface DoctrineDatabaseAssistantInterface
{
    /**
     * @return string[]
     */
    public function getSchemaNames(): array;

    /**
     * @param string $schemaName
     *
     * @return ConnectionInterface
     */
    public function getConnection(string $schemaName): ConnectionInterface;

    /**
     * @param string|null $schemaName
     *
     * @return AbstractSchemaManager
     * @throws Exception
     */
    public function getDoctrineSchemaManager(?string $schemaName = null): AbstractSchemaManager;

    /**
     * @return RelieseConfiguration
     */
    public function getRelieseConfiguration(): RelieseConfiguration;
}
