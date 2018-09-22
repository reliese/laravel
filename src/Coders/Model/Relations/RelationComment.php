<?php

/**
 * Created by Mike Artemiev
 * Date: 9/22/18 5:40 PM.
 */

namespace Reliese\Coders\Model\Relations;

trait RelationComment
{
    /**
     * @return string
     */
    public function comment()
    {
        return $this->related->getBlueprint()->tableComment();
    }
}