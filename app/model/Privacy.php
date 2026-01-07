<?php

namespace App\Model;

class Privacy extends BaseModel
{
    /** @const STRICT_PRIVATE int */
    public const STRICT_PRIVATE = 1;

    /** @const ONLY_FRIENDS int */
    public const ONLY_FRIENDS = 2;

    /** @const FRIENDS_AND_GROUPS int */
    public const FRIENDS_AND_GROUPS = 3;

    /** @const PUBLIC_IN_SYSTEM int */
    public const PUBLIC_IN_SYSTEM = 4;

    /** @const PUBLIC_FOR_ALL int */
    public const PUBLIC_FOR_ALL = 5;


    /**
     * @return mixed
     */
    public function getList()
    {
        return $this->findActivePairs();
    }
}
