<?php

/**
 * Created by Cristian.
 * Date: 02/10/16 11:06 PM.
 */

namespace Pursehouse\Modeler\Meta;

interface Column
{
    /**
     * @return \Illuminate\Support\Fluent
     */
    public function normalize();
}
