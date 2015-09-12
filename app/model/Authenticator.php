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
        list($username, $password) = $credentials;

        $row = $this->database->table(self::TABLE_NAME)
                ->where(self::COLUMN_NAME, $username)
                ->select('user.*, role.name AS role')
                ->fetch();

        if (!$row) {
                throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

        } elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
                throw new Nette\Security\AuthenticationException('Your password is incorrect.', self::INVALID_CREDENTIAL);
        }
        //elseif (Passwords::needsRehash($user['password'])) {
        // $user->update(array(
        //         self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
        // ));
        //}

        $arr = $row->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
    }
}
