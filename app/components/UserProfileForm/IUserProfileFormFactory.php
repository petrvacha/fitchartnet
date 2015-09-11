<?php


interface IUserProfileFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\UserProfileForm
     */
    function create($userId);
}