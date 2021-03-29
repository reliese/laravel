<?php

/**
 * Created by Cristian.
 * Date: 05/09/16 11:27 PM.
 */

namespace Reliese\Coders\Model;

interface Relation
{
    /**
     * @return string
     */
    public function hint(): string;

    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return string
     */
    public function body(): string;
}
