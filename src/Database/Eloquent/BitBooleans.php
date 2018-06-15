<?php

/**
 * Created by Cristian.
 * Date: 09/10/16 07:34 PM.
 */

namespace Reliese\Database\Eloquent;

trait BitBooleans
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function fromBool($value)
    {
        if ($value === "\x00") {
            return false;
        }
        if ($value === "\x01") {
            return true;
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function fromBoolean($value)
    {
        return $this->fromBool($value);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function toBool($value)
    {
        if ($value === false) {
            return "\x00";
        }
        if ($value === true) {
            return "\x01";
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function toBoolean($value)
    {
        return $this->toBool($value);
    }
}
