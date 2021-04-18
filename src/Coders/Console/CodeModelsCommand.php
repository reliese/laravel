<?php

namespace Reliese\Coders\Console;

/**
 * Class CodeModelsCommand
 * @deprecated Use \Reliese\Command\Model\MakeModelsCommand instead, this only provides an alias
 * @package Reliese\Coders\Console
 */
class CodeModelsCommand extends \Reliese\Command\Model\ModelGenerateCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:models
                            {--s|schema= : The name of the MySQL database}
                            {--c|connection= : The name of the connection}
                            {--t|table= : The name of the table}';
}
