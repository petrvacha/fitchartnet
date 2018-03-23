<?php

namespace App\Model;

use Fitchart\Application\InvalidArgumentException;
use Fitchart\Application\SecurityException;
use Nette\Security\Passwords;
use Fitchart\Application\Utilities;
use \Nette\Utils\ArrayHash;
use Nette\Utils\Image;

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

    /** @const AVATAR_SIZE_WIDTH int */
    const AVATAR_SIZE_WIDTH = 250;

    /** @const AVATAR_SIZE_HEIGHT int */
    const AVATAR_SIZE_HEIGHT = 250;


    /** @var \App\Model\Privacy */
    protected $privacyModel;

    /** @var \App\Model\Role */
    protected $roleModel;

    /** @var \App\Model\Friend */
    protected $friendModel;

    /** @var \App\Model\FriendshipRequest */
    protected $friendshipRequest;

    /** @var \App\Model\Role $roleModel */
    protected $user;


    /**
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     * @param \App\Model\Privacy $privacyModel
     * @param \App\Model\Role $roleModel
     * @param \App\Model\Friend $friendModel
     * @param \App\Model\FriendshipRequest $friendshipRequest
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user,
                                Privacy $privacyModel,
                                Role $roleModel,
                                Friend $friendModel,
                                FriendshipRequest $friendshipRequest)
    {
        parent::__construct($context);
        $this->user = $user;
        $this->privacyModel = $privacyModel;
        $this->roleModel = $roleModel;
        $this->friendModel = $friendModel;
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
            $data['photo']->move(USER_ORIGIN_AVATAR_DIR . '/' . $fileName);
            $image = $data['photo']->toImage();
            $image->resize(self::AVATAR_SIZE_WIDTH, self::AVATAR_SIZE_HEIGHT, $image::SHRINK_ONLY);

            $image->save(USER_AVATAR_DIR . '/' . $fileName);
            $this->findRow($data['userId'])->update(['profile_photo' => $fileName]);
            $this->user->getIdentity()->profile_photo = $fileName;

        } else {
            throw new \Fitchart\Application\DataException('An error occurred in the upload.');
       }
    }

    /**
     * @param int $left
     * @param int $top
     * @param int $width
     * @param int $height
     */
    public function cropPhoto($left, $top, $width, $height)
    {
        $profilePhoto = $this->user->getIdentity()->profile_photo;
        $image = Image::fromFile(USER_ORIGIN_AVATAR_DIR . '/' . $profilePhoto);
        $image->crop($left, $top, $width, $height);
        $image->save(USER_AVATAR_DIR . '/' . $profilePhoto);
        $this->user->getIdentity()->profile_photo = $profilePhoto;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function hasPermissionForUser($userId)
    {
        return $this->user->getIdentity()->role == Role::ADMIN ||
                $this->user->getIdentity()->role == Role::SUPERADMIN ||
                $this->friendModel->areFriends($userId);
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
     * @return bool
     */
    public function getUserLoginData($id)
    {
        $row = $this->findBy(['user.id' => $id])
                ->select('user.*, role.id AS role')
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
    public function getFreeUsers()
    {
        $privacyModel = $this->privacyModel;
        $userId = $this->user->getIdentity()->id;
        return $this->context->query("
            SELECT
                U.id,
                U.username,
                U.firstname,
                U.surname,
                U.email,
                U.gender_id,
                U.last_action,
                U.bio,
                U.profile_photo
            FROM
                user U
            LEFT JOIN friend F ON
                U.id = F.user_id AND F.user_id2 = ? OR U.id = F.user_id2 AND F.user_id = ?
            LEFT JOIN friendship_request FR ON
                (U.id = FR.to_user_id AND FR.from_user_id = ? OR U.id = FR.from_user_id AND FR.to_user_id = ?) AND (FR.approved IS NULL OR FR.approved = 1)
            WHERE
                U.privacy_id <= ? AND
                U.active = 1 AND
                U.id <> ? AND
                F.id is NULL AND
                FR.id is NULL",
            $userId,
            $userId,
            $userId,
            $userId,
            $privacyModel::PUBLIC_IN_SYSTEM,
            $userId
        )->fetchAll();

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

    /**
     * @param bool $transform
     * @return mixed
     */
    public function getAvailableUsers($transform = FALSE)
    {
        if ($this->user->getIdentity()->role <= Role::ADMIN) {
            $users = $this->getTable()->where('id <> ?', $this->user->getIdentity()->id)->fetchPairs('id', 'username');
        } else {
            $users = $this->getTable()->where(':friend.user_id2 = ?', $this->user->getIdentity()->id)->fetchPairs('id', 'username');
        }

        if ($users && $transform) {
            $transformUsers = [];
            foreach ($users as $id => $name) {
                $transformUsers[] = ['id' => $id, 'name' => $name];
            }
            return $transformUsers;
        }
        return $users;
    }

    /**
     * @param $values
     * @return array
     * @throws InvalidArgumentException
     */
    public function prepareResetToken($values)
    {
        if (isset($values['email'])) {
            $user = $this->getTable()
                ->where('username  = ? OR email = ?', $values['email'], $values['email'])
                ->select('*')
                ->fetch();
            if ($user) {
                $data = [
                    'token' => Utilities::create_sha1_hash($values->email, $this->getDateTime()),
                ];
                $this->findRow($user['id'])->update($data);
                $data['email'] = $user['email'];
                $data['username'] = $user['username'];
                return $data;
            }
        }

        if (strpos($values['email'], '@')) {
            throw new InvalidArgumentException('Email was not found.');
        } else {
            throw new InvalidArgumentException('Username was not found.');
        }
    }

    /**
     * @param string $token
     * @return bool
     */
    public function checkToken($token)
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * @param $values
     * @throws SecurityException
     */
    public function updateUserPassword($values)
    {
        $user = empty($values['token']) ? FALSE : $this->findOneBy(['token' => $values['token']]);

        if ($user) {
            if ($values['password'] === $values['confirm_password']) {
                $user->update([
                    'password' => Passwords::hash($values['password']),
                    'token' => NULL
                    ]);
            } else {
                throw new SecurityException('The passwords are not the same.');
            }
        } else {
            throw new SecurityException('The token is invalid.');
        }
    }
}