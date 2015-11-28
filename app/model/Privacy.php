<?php

namespace App\Model;

class Privacy extends BaseModel
{
    /** @const STRICT_PRIVATE int */
    const STRICT_PRIVATE = 1;

    /** @const ONLY_FRIENDS int */
    const ONLY_FRIENDS = 2;

    /** @const FRIENDS_AND_GROUPS int */
    const FRIENDS_AND_GROUPS = 3;

    /** @const PUBLIC_IN_SYSTEM int */
    const PUBLIC_IN_SYSTEM = 4;

    /** @const PUBLIC_FOR_ALL int */
    const PUBLIC_FOR_ALL = 5;


    /**
     * @return mixed
     */
    public function getList()
    {
        return $this->findActivePairs();
    }
}