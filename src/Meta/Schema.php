<?php

namespace Pursehouse\Modeler\Meta;

interface Schema
{
    /**
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function connection();

    /**
     * @return string
     */
    public function schema();

    /**
     * @return \Pursehouse\Modeler\Meta\Blueprint[]
     */
    public function tables();

    /**
     * @param string $table
     *
     * @return bool
     */
    public function has($table);

    /**
     * @param string $table
     *
     * @return \Pursehouse\Modeler\Meta\Blueprint
     */
    public function table($table);

    /**
     * @param \Pursehouse\Modeler\Meta\Blueprint $table
     *
     * @return array
     */
    public function referencing(Blueprint $table);
}
