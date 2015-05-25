<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;

class User extends BaseModel
{
    
    /** @const USER_STATE_NEW string */
    const USER_STATE_NEW = 'new';

    /** @const USER_STATE_ACTIVE string */
    const USER_STATE_ACTIVE = 'active';

    /**
     * Adds new user.
     * @param  string $values
     */
    public function add($values)
    {
        $this->context->table($this->getTableName())->insert(array(
           'username' => $values->username,
           'password' => Passwords::hash($values->password),
           'email' => $values->email,
           'state' => self::USER_STATE_NEW,
           'createdAt' => $this->getDateTime(),
           'updateAt' => $this->getDateTime()
        ));
    }
}