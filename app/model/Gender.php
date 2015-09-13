<?php

namespace App\Model;

class Gender extends BaseModel
{
    /**
     * @return 
     */
    public function getList()
    {
        return $this->findPairs();
    }
}