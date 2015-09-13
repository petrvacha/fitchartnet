<?php

namespace App\Components\UserProfileForm;


interface IUserProfileFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\UserProfileForm
     */
    function create($userId);
}