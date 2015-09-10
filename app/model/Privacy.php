<?php

namespace App\Model;

class Privacy extends BaseModel
{
    /** @const PRIVACY_SUPERADMIN int */
    const PRIVACY_SUPERADMIN = 1;

    /** @const PRIVACY_ADMIN int */
    const PRIVACY_ADMIN = 2;

    /** @const PRIVACY_MODERATOR int */
    const PRIVACY_MODERATOR = 3;

    /** @const PRIVACY_USER int */
    const PRIVACY_USER = 4;

    /** @const PRIVACY_SPECTATOR int */
    const PRIVACY_SPECTATOR = 5;
}