<?php

namespace GiveIt\SDK;

class Object extends Base
{
    public function __construct($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }
}
