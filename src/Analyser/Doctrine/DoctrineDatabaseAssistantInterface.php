<?php

namespace Reliese\Analyser\Doctrine;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\ConnectionInterface;

/**
 * Interface DoctrineDatabaseAssistantInterface
 */
interface DoctrineDatabaseAssistantInterface
{
    public function getSchemaNames(): array;

    public function getConnection(string $schemaName): ConnectionInterface;

    /**
     * @param string|null $schemaName
     *
     * @return AbstractSchemaManager
     * @throws \Doctrine\DBAL\Exception
     */
    public function getDoctrineSchemaManager(?string $schemaName = null) : AbstractSchemaManager;
}
