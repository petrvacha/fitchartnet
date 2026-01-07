<?php

namespace App\Model;

/**
 * Activity Model
 */
class Activity extends BaseModel
{
    public function getList()
    {
        return $this->findActivePairs();
    }
}
