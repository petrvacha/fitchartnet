<?php

namespace App\Model;

use Nette\Security\Passwords;
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

    /** @const API_TOKEN_LENGTH int */
    const API_TOKEN_LENGTH = 6;

    
    /** @var \App\Model\Privacy */
    protected $privacyModel;

    /** @var \App\Model\Role */
    protected $roleModel;

    /** @var \App\Model\FriendshipRequest */
    protected $friendshipRequest;
    
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
                                \App\Model\Role $roleModel,
                                \App\Model\FriendshipRequest $friendshipRequest)
    {
        parent::__construct($context);
        $this->user = $user;
        $this->privacyModel = $privacyModel;
        $this->roleModel = $roleModel;
        $this->friendshipRequest = $friendshipRequest;
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
            'api_token' => $this->getFreeApiToken($values->username),
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
     * @param string $hash
     * @return string
     */
    public function getFreeApiToken($hash)
    {
        do {
            $apiToken = Utilities::create_sha1_hash($hash, $this->getDateTime(), self::API_TOKEN_LENGTH);
        } while ($this->findBy(['api_token' => $apiToken])->fetch());

        return $apiToken;
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
                ->update(['token' => NULL, 'active' => TRUE, 'updated_at' => $this->getDateTime()]);
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
                          user.gender_id,
                          user.api_token,
                          gender.name AS gender,
                          privacy.description AS privacy_description,
                          privacy.name AS privacy_name')
                ->fetch();
    }

    /**
     * @param Nette\Utils\ArrayHash $data
     * @throws SecurityException
     */
    public function updateUserData($data)
    {
        $user = $this->findRow($data['id']);
        if (!empty($data['password']) && !empty($data['confirm_password'])) {

            if (empty($data['old_password']) && !empty($user->password) ||
                !empty($data['old_password']) && !Passwords::verify($data['old_password'], $user->password)) {
                throw new \Fitchart\Application\SecurityException('Password is incorrect.');
            }
            $data['password'] = Passwords::hash($data['password']);

        } else {
            unset($data['password']);
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

    /**
     * @param int $id
     * @return ActiveRow
     */
    public function findByFacebookId($id)
    {
        return $this->findBy(['facebook_id' => $id])->fetch();
    }

    /**
     * @param ArrayHash $data
     * @return ArrayHash
     */
    public function registerFromFacebook(\Nette\Utils\ArrayHash $data)
    {
        $roleModel = $this->roleModel;
        $privacyModel = $this->privacyModel;

        $existingUser = $this->findBy(['email' => $data['email']]);
        $existingUserData = $existingUser->fetch();
        if ($existingUserData) {
            $update = ['facebook_id' => $data['id'], 'updated_at' => $this->getDateTime()];
            if (!empty($existingUserData['profile_photo'])) {
                $fileName = $existingUserData['id'] . '.jpg';
                Utilities::storeFile($data['picture']['data']['url'], USER_AVATAR_DIR . '/' . $fileName);
                $update['profile_photo'] = $fileName;
            }

            $existingUser->update($update);
            return $existingUserData;
            
        } else {
            $insert = [
                'facebook_id' => $data['id'],
                'email' => $data['email'],
                'firstname' => $data['first_name'],
                'surname' => $data['last_name'],
                'username' => $this->findFreeUsername($data['first_name'], $data['last_name']),
                'api_token' => $this->getFreeApiToken($data['last_name']),
                'privacy_id' => $privacyModel::FRIENDS_AND_GROUPS,
                'role_id' => $roleModel::USER,
                'state' => self::USER_STATE_NEW,
                'active' => TRUE,
                'created_at' => $this->getDateTime(),
                'updated_at' => $this->getDateTime()
            ];

            $this->insert($insert);

            $user = $this->findBy(['facebook_id' => $data['id']]);
            $userData = $user->fetch();
            $fileName = $userData['id'] . '.jpg';
            Utilities::storeFile($data['picture']['data']['url'], USER_AVATAR_DIR . '/' . $fileName);
            $update['profile_photo'] = $fileName;
            $user->update($update);

            return $this->findBy(['facebook_id' => $data['id']])->fetch();
        }
    }

    /**
     * @param string $firstname
     * @param string $surname
     * @return string
     */
    protected function findFreeUsername($firstname, $surname)
    {
        $firstname = \Nette\Utils\Strings::webalize($firstname);
        $surname = \Nette\Utils\Strings::webalize($surname);

        $user = $this->findBy(['username' => $firstname.$surname])->fetch();
        if ($user) {
            $number = 1;
            do {
                $user = $this->findBy(['username' => $firstname.$surname.$number])->fetch();
                $number++;
            } while ($user);
            return $firstname.$surname.$number--;
            
        } else {
            return $firstname.$surname;
        }
    }

    /**
     * @param int $id
     * @param string $accessToken
     */
    public function updateFacebookAccessToken($id, $accessToken)
    {
        $user = $this->findByFacebookId($id);
        $user->update(['facebook_access_token' => $accessToken]);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function getUserLoginData($id)
    {
        $row = $this->findBy(['user.id' => $id])
                ->select('user.*, role.name AS role')
                ->fetch();

        if ($row) {
            $arr = $row->toArray();
            $weight = $row->related('weight.user_id')->fetch();
            if ($weight) {
                $arr['weight'] = $weight->value;
                $arr['weight_last_update'] = $weight->datetime;
            }
            return $arr;
        }

        return FALSE;
    }

    /**
     * @param string $subject
     * @return ArrayHash
     */
    public function getUserList($subject = NULL)
    {
        $privacyModel = $this->privacyModel;
        $userId = $this->user->getIdentity()->id;
        $query = "privacy_id <= ? AND id <> ?";
        if (!empty($subject)) {
            $query .= " AND (firstname LIKE ? OR surname LIKE ? OR username LIKE ? OR CONCAT(firstname, ' ', surname) LIKE ?)";
            return $this->getTable()->where($query, $privacyModel::PUBLIC_IN_SYSTEM, $userId, $subject, $subject, $subject, $subject)->fetchAll();
        }
        
        return $this->getTable()->where($query, $privacyModel::PUBLIC_IN_SYSTEM, $userId)->fetchAll();
    }

    /**
     * @return ArrayHash
     */
    public function getFriendList()
    {
        return $this->getTable()->where(':friend.user_id2 = ?', $this->user->getIdentity()->id)->fetchAll();
    }

    /**
     * @return ArrayHash
     */
    public function getFriendshipRequestList()
    {
        return $this->getTable()->where(':friendship_request.approved IS NULL AND :friendship_request.from_user_id = ?', $this->user->getIdentity()->id)->fetchAll();
    }

    /**
     * @return ArrayHash
     */
    public function getFriendshipOfferList()
    {
        $toUserId = $this->user->getIdentity()->id;
        $inIDs = $this->context->table('friendship_request')->where('approved IS NULL AND to_user_id', $toUserId)->select('from_user_id id')->fetchAll();
        return $this->getTable()->where('id', $inIDs)->select('*')->fetchAll();
    }
}