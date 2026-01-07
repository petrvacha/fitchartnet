<?php

namespace App\Components\ActivityForm;

interface IActivityFormFactory
{
    /**
     * @param int $userId
     * @param int $challengeId
     * @return \App\Components\ActivityForm
     */
    public function create($userId, $challengeId);
}
