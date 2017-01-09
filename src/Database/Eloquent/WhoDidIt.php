<?php

/**
 * Created by Cristian.
 * Date: 12/10/16 12:09 AM.
 */

namespace Reliese\Database\Eloquent;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model as Eloquent;

class WhoDidIt
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Blamable constructor.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $event
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function creating($event, Eloquent $model)
    {
        $model->created_by = $this->doer();
    }

    /**
     * @param string $event
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function updating($event, Eloquent $model)
    {
        $model->udpated_by = $this->doer();
    }

    /**
     * @return mixed|string
     */
    protected function doer()
    {
        if (app()->runningInConsole()) {
            return 'CLI';
        }

        return $this->authenticated() ? $this->userId() : '????';
    }

    /**
     * @return mixed
     */
    protected function authenticated()
    {
        return $this->request->user();
    }

    /**
     * @return mixed
     */
    protected function userId()
    {
        return $this->authenticated()->id;
    }
}
