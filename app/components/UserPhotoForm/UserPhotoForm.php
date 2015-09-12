<?php

namespace App\Components;

use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;


class UserPhotoForm extends \Fitchart\Application\Control
{
    /** @var int */
    protected $userId;
    
    /** @var \App\Model\User */
    protected $userModel;

    
    /**
     * @param int $userId
     * @param \App\Model\User $userModel
     */
    public function __construct($userId,
                                \App\Model\User $userModel)
    {
        parent::__construct();
        $this->userId = $userId;
        $this->userModel = $userModel;
    }

    /**
     * @return Form
     */
    public function createComponentUserPhotoForm()
    {
        $form = new Form;
        $form->addUpload('photo', 'Photo:')
            ->addRule(Form::IMAGE, 'File has to be JPEG, PNG or GIF.')
            ->getControlPrototype()->class = 'form-control';

        $form->addSubmit('upload', 'Upload')
            ->getControlPrototype()->class = 'btn btn-success';

        $form->onSuccess[] = array($this, 'formSent');
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->user = $this->userModel->getUserData($this->userId);
        $this->template->render();
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function formSent(Form $form, ArrayHash $values)
    {
        $values['userId'] = $this->userId;
        try {
            $this->userModel->updatePhoto($values);

        } catch (\Fitchart\Application\SecurityException $e) {
            $form->addError($e->getMessage());
        } catch (\Fitchart\Application\DataException $e) {
            $form->addError($e->getMessage());
        }
    }
}