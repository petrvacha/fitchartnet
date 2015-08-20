<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Pushupers\Application\Utilities;

class User extends BaseModel
{
    /** @const USER_STATE_NEW string */
    const USER_STATE_NEW = 'new';

    /** @const USER_STATE_ACTIVE string */
    const USER_STATE_ACTIVE = 'active';

    /** @const ACTIVE_BY_TOKEN string */
    const ACTIVE_BY_TOKEN = 'token';

    /** @const ACTIVE_BY_ID string */
    const ACTIVE_BY_ID = 'id';


    /**
     * Adds new user.
     * @param array $values
     * @return array
     */
    public function add($values)
    {
        $insert = [
            'username' => $values->username,
            'password' => Passwords::hash($values->password),
            'email' => $values->email,
            'token' => Utilities::create_sha1_hash($values->email, $this->getDateTime()),
            'state' => self::USER_STATE_NEW,
            'createdAt' => $this->getDateTime(),
            'updateAt' => $this->getDateTime()
        ];
        $this->context->table($this->getTableName())->insert($insert);

        unset($insert['password']);
        return $insert;
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    public function activeBy($type, $value)
    {
        if ($type === self::ACTIVE_BY_TOKEN || $type === self::ACTIVE_BY_ID) {
            $user = $this->findOneBy(self::ACTIVE_BY_TOKEN, $value);
        }

        if ($user) {
            return $user->update('active', 1);
        } else {
            return FALSE;
        }
    }

    /**
     * @param string $token
     * @return bool
     */
    public function activeUserByToken($token)
    {
        return $this
                ->findOneBy(['token' => $token])
                ->update(['token' => NULL, 'active' => TRUE, 'updateAt' => $this->getDateTime()]);
    }
}