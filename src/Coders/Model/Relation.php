<?php

namespace Pursehouse\Modeler\Coders\Model;

interface Relation
{
    /**
     * @return string
     */
    public function hint();

    /**
     * @return string
     */
    public function name();

    /**
     * @return string
     */
    public function body();
}
