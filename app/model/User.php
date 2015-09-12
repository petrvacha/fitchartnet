<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Fitchart\Application\Utilities;

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

    
    /** @var \App\Model\Privacy */
    protected $privacyModel;

    /** @var \App\Model\Role */
    protected $roleModel;
    
    /** @var \App\Model\Role $roleModel */
    protected $user;

            
    /**
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     * @param \App\Model\Privacy $privacyModel
     * @param \App\Model\Role $roleModel
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user,
                                \App\Model\Privacy $privacyModel,
                                \App\Model\Role $roleModel)
    {
        parent::__construct($context);
        $this->user = $user;
        $this->privacyModel = $privacyModel;
        $this->roleModel = $roleModel;
    }

    /**
     * Adds new user.
     * @param array $values
     * @return array
     */
    public function add($values)
    {
        $roleModel = $this->roleModel;
        $privacyModel = $this->privacyModel;
        $insert = [
            'username' => $values->username,
            'password' => Passwords::hash($values->password),
            'email' => $values->email,
            'token' => Utilities::create_sha1_hash($values->email, $this->getDateTime()),
            'privacy_id' => $privacyModel::FRIENDS_AND_GROUPS,
            'role_id' => $roleModel::USER,
            'state' => self::USER_STATE_NEW,
            'created_at' => $this->getDateTime(),
            'updated_at' => $this->getDateTime()
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
                ->update(['token' => NULL, 'active' => TRUE, 'update_at' => $this->getDateTime()]);
    }

    /**
     * @param int $userId
     * @return ActiveRow
     */
    public function getUserData($userId)
    {
        return $this
                ->getTable()
                ->where('user.id = ?', $userId)
                ->select('user.id, 
                          user.firstname,
                          user.surname,
                          user.email,
                          user.username,
                          user.bio,
                          user.privacy_id,
                          user.profile_photo,
                          privacy.description AS privacy_description,
                          privacy.name AS privacy_name')
                ->fetch();
    }

    /**
     * @param ArrayHash $data
     * @throws SecurityException
     */
    public function updateUserData($data)
    {
        $user = $this->findRow($data['id']);
        if (!empty($data['old_password']) && !empty($data['password']) && !empty($data['confirm_password'])) {
            if (!Passwords::verify($data['old_password'], $user->password)) {
                throw new \Fitchart\Application\SecurityException('Password is incorrect.');
            }
            $data['password'] = Passwords::hash($data['password']);
        }
        unset($data['old_password']);
        unset($data['confirm_password']);
        
        $data['updated_at'] = $this->getDateTime();
        $user->update($data);
    }

    /**
     * @param ArrayHash $data
     * @throws \Fitchart\Application\DataException
     */
    public function updatePhoto($data)
    {
        if ($data['photo']->isOk()) {
            $extension = Utilities::getFileExtension($data['photo']->getName());
            $fileName = $data['userId'] . '.' . $extension;
            $data['photo']->move(USER_AVATAR_DIR . '/' . $fileName);

            $this->findRow($data['userId'])->update(['profile_photo' => $fileName]);
            $this->user->getIdentity()->profile_photo = $fileName;
        } else {
            throw new \Fitchart\Application\DataException('An error occurred in the upload.');
       }
    }
}