<?php

namespace App\Components\UserPhotoForm;

interface IUserPhotoFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\UserPhotoForm
     */
    public function create($userId);
}
