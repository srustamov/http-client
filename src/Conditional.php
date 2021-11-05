<?php

namespace Srustamov\HttpClient;

trait Conditional
{

    public function when($condition, $callback)
    {
        if ($condition) {
            $callback($this, $condition);
        }

        return $this;
    }


    public function unless($condition, $callback)
    {
        return $this->when(!$condition, $callback);
    }


    public function whenTrue($condition, $callback)
    {
        if ($condition === true) {
            return $this->when(...func_get_args());
        }

        return $this;
    }

    public function whenFalse($condition, $callback)
    {
        if ($condition === false) {
            return $this->when(...func_get_args());
        }

        return $this;
    }

    public function whenNull($condition, $callback)
    {
        if ($condition === null) {
            return $this->when(...func_get_args());
        }

        return $this;
    }
}