<?php

namespace App\Model;

use Nette;
use Nette\Utils\Strings;
use Nette\Security\Passwords;


/**
 * Authenticator
 */
class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator
{
    const TABLE_NAME = 'user';
    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'username';
    const COLUMN_EMAIL = 'email';
    const COLUMN_PASSWORD_HASH = 'password';
    const COLUMN_ROLE = 'role';


    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    /**
     * Performs an authentication.
     * @param array $credentials
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($login, $password) = $credentials;

        $row = $this->database->table(self::TABLE_NAME)
                ->where(self::COLUMN_NAME . '= ? OR ' . self::COLUMN_EMAIL . '= ?', $login, $login)
                ->select('user.*, role.name AS role')
                ->fetch();
        
        if (!$row) {
            if (strpos($login, '@')) {
                throw new Nette\Security\AuthenticationException('The email is incorrect.', self::IDENTITY_NOT_FOUND);
            } else {
                throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
            }

        } elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('Your password is incorrect.', self::INVALID_CREDENTIAL);
        }
        //elseif (Passwords::needsRehash($user['password'])) {
        // $user->update(array(
        //         self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
        // ));
        //}

        $arr = $row->toArray();
        $weight = $row->related('weight.user_id')->fetch();
        if ($weight) {
            $arr['weight'] = $weight->value;
            $arr['weight_last_update'] = $weight->datetime;
        }
        
        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
    }
}
