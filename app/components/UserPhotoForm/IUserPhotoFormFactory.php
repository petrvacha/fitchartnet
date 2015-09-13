<?php

namespace App\Components\UserPhotoForm;


interface IUserPhotoFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\UserPhotoForm
     */
    function create($userId);
}