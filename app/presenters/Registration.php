<?php


namespace App\Presenters;


class RegistrationPresenter extends BasePresenter
{
    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Components\RegistrationForm\IRegistrationFormFactory @inject */
    public $registrationFormFactory;


    /**
     * @param \App\Model\User $userModel
     */
    public function __construct(\App\Model\User $userModel)
    {
        parent::__construct();
        $this->userModel = $userModel;
    }

    public function renderDefault()
    {
        $this->template->title = 'registration';
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Challenge:default');
        }
        $this->setLayout('authLayout');
    }

    public function renderError()
    {

    }

    /**
     * @param string $hash
     */
    public function actionCheck($hash)
    {
        $result = $this->userModel->activeUserByToken($hash);

        if ($result) {
            $this->flashMessage('Congratulations, your account has been activated!', parent::MESSAGE_TYPE_INFO);
            $this->redirect('Homepage:default');
        } else {
            $this->flashMessage('We are sorry, your activated link is wrong.', parent::MESSAGE_TYPE_ERROR);
            $this->redirect('Registration:error');
        }
    }

    /**
     * Registration form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentRegistrationForm()
    {
        $control = $this->registrationFormFactory->create();
        $control->getComponent('registrationForm')->onSuccess[] = function() {
            $this->flashMessage('Check your spam box and confirm the registration.', 'info');
            $this->redirect('Registration:');
        };
        return $control;
    }
}