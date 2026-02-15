<?php

namespace App\Components\GroupForm;

interface IGroupFormFactory
{
    /**
     * @param int $userId
     * @return \App\Components\GroupForm
     */
    public function create($userId);
}
