<?php

namespace App\Model;

class Role extends BaseModel
{
    /** @const SUPERADMIN int */
    public const SUPERADMIN = 1;

    /** @const ADMIN int */
    public const ADMIN = 2;

    /** @const MODERATOR int */
    public const MODERATOR = 3;

    /** @const USER int */
    public const USER = 4;

    /** @const SPECTATOR int */
    public const SPECTATOR = 5;
}
