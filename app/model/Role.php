<?php

namespace App\Model;

class Role extends BaseModel
{
    /** @const SUPERADMIN int */
    const SUPERADMIN = 1;

    /** @const ADMIN int */
    const ADMIN = 2;

    /** @const MODERATOR int */
    const MODERATOR = 3;

    /** @const USER int */
    const USER = 4;

    /** @const SPECTATOR int */
    const SPECTATOR = 5;
}