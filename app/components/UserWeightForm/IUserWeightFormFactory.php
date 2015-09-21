<?php

namespace App\Components\UserWeightForm;


interface IUserWeightFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\UserWeightForm
     */
    function create($userId);
}