<?php

/**
 * Created by Cristian.
 * Date: 11/09/16 09:00 PM.
 */

namespace Reliese\Coders\Model;

use Illuminate\Support\Arr;
use Reliese\Meta\Blueprint;

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
     * @param \Reliese\Meta\Blueprint $blueprint
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(Blueprint $blueprint, $key, $default = null)
    {
        $original = Arr::get($this->config, "*.$key", $default);

        $checkKeys = [
            "{$blueprint->schema()}.$key",
            "{$blueprint->qualifiedTable()}.$key",
            "connections.{$blueprint->connection()}.$key",
            "connections.{$blueprint->connection()}.{$blueprint->schema()}.$key",
            "connections.{$blueprint->connection()}.{$blueprint->table()}.$key",
        ];

        foreach ($checkKeys as $key) {
            $original = Arr::get($this->config, $key, $original);
        }

        return $original;
    }
}
