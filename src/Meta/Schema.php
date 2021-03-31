<?php

/**
 * Created by Cristian.
 * Date: 02/10/16 07:56 PM.
 */

namespace Reliese\Meta;

use Illuminate\Database\Connection;

interface Schema
{
    /**
     * @return Connection
     */
    public function connection();

    /**
     * @return string
     */
    public function schema(): string;

    /**
     * @return Blueprint[]
     */
    public function tables(): array;

    /**
     * @param string $table
     *
     * @return bool
     */
    public function has(string $table): bool;

    /**
     * @param string $table
     *
     * @return Blueprint
     */
    public function table(string $table): Blueprint;

    /**
     * @param Blueprint $table
     *
     * @return RelationBag[]
     */
    public function referencing(Blueprint $table): array;
}
