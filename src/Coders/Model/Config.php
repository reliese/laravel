<?php

namespace Pursehouse\Modeler\Coders\Model;

use Illuminate\Support\Arr;
use Pursehouse\Modeler\Meta\Blueprint;

class Config
{
    /**
     * @var array
     */
    protected $config;

    /**
     * ModelConfig constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * @param \Pursehouse\Modeler\Meta\Blueprint $blueprint
     * @param string                  $key
     * @param mixed                   $default
     *
     * @return mixed
     */
    public function get(Blueprint $blueprint, $key, $default = null)
    {

        $default    = Arr::get($this->config, "*.$key",                                 $default    );
        $connection = Arr::get($this->config, "{$blueprint->connection()}.$key",        $default    );
        $schema     = Arr::get($this->config, "{$blueprint->schema()}.$key",            $connection );
        $specific   = Arr::get($this->config, "{$blueprint->qualifiedTable()}.$key",    $schema     );

        return $specific;

    }
}
