<?php

namespace App\Components\UserProfileForm;

interface IUserProfileFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\UserProfileForm
     */
    public function create($userId);
}
