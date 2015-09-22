<?php

namespace App\Model;


class Weight extends BaseModel
{
    /** @var \Nette\Security\User */
    protected $user;

    
    /**
     * @param \Nette\Security\User $user
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user)
    {
        parent::__construct($context);
        $this->user = $user;
    }
    
    /**
     * @param int $userId
     * @return Nette\Database\Table\ActiveRow
     */
    public function getUserLastWeight($userId)
    {
        return $this
                    ->findBy(['user_id' => $userId])
                    ->order('datetime DESC')
                    ->fetch();
    }

    /**
     * @param Nette\Utils\ArrayHash $values
     */
    public function insertWeight($values)
    {
        $values['datetime'] = $this->getDateTime();
        $this->insert($values);
        $this->user->getIdentity()->weight = $values['value'];
    }

    /**
     * @param int $id
     * @param int $userId
     */
    public function deleteWeight($id, $userId)
    {
        $this->findBy(['id' => $id, 'user_id' => $userId])->delete();
    }
    
}