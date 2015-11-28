<?php

namespace App\Model;

class Gender extends BaseModel
{
    /**
     * @return mixed
     */
    public function getList()
    {
        return $this->findPairs();
    }
}