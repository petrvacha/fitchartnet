<?php

namespace App\Model;


class LaunchAlert extends BaseModel
{
    /**
     * Adds en email for the launch alert
     * @param ArrayHash $values
     */
    public function insertEmail($values)
    {
        $insert = [
            'email' => $values->email,
            'created_at' => $this->getDateTime()
        ];
        $this->context->table($this->getTableName())->insert($insert);
    }
}