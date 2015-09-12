<?php


interface IActivityFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\ActivityForm
     */
    function create($userId);
}