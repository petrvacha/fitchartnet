<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;

class User extends BaseModel
{    
    /**
     * Adds new user.
     * @param  string $username
     * @param  string $password
     * @return void
     */
    public function add($username, $password)
    {
        $this->context->table($this->getTableName())->insert(array(
           'username' => $username,
           'password' => Passwords::hash($password),
        ));
    }
}