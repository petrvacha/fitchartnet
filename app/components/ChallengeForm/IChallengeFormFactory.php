<?php

namespace App\Components\ChallengeForm;


interface IChallengeFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\ChallengeForm
     */
    function create($userId);
}