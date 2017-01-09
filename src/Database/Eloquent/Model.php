<?php

/**
 * Created by Cristian.
 * Date: 01/10/16 03:02 PM.
 */

namespace Reliese\Database\Eloquent;

class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * {@inheritdoc}
     */
    protected function castAttribute($key, $value)
    {
        if ($this->hasCustomGetCaster($key)) {
            return $this->{$this->getCustomGetCaster($key)}($value);
        }

        return parent::castAttribute($key, $value);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function hasCustomGetCaster($key)
    {
        return $this->hasCast($key) && method_exists($this, $this->getCustomGetCaster($key));
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function getCustomGetCaster($key)
    {
        return 'from'.ucfirst($this->getCastType($key));
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasCustomSetCaster($key)) {
            $value = $this->{$this->getCustomSetCaster($key)}($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function hasCustomSetCaster($key)
    {
        return $this->hasCast($key) && method_exists($this, $this->getCustomSetCaster($key));
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function getCustomSetCaster($key)
    {
        return 'to'.ucfirst($this->getCastType($key));
    }
}
