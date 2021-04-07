<?php

namespace Reliese\Analyser\Doctrine;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;

/**
 * Class ConnectionAssistant
 */
class ConnectionAssistant
{
    /**
     * @var ConnectionFactory
     */
    private ConnectionFactory $connectionFactory;

    /**
     * @var DatabaseManager
     */
    private DatabaseManager $databaseManager;

    public function __construct(
        DatabaseManager $databaseManager,
        ConnectionFactory $connectionFactory,
    ) {
        $this->databaseManager = $databaseManager;
        $this->connectionFactory = $connectionFactory;
    }
}
