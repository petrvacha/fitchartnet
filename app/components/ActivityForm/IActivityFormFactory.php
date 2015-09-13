<?php

namespace App\Components\ActivityForm;


interface IActivityFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\ActivityForm
     */
    function create($userId);
}