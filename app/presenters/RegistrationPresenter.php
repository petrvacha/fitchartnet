<?php


namespace App\Presenters;


use App\Model\Challenge;
use App\Model\Friend;
use Fitchart\Application\Utilities;
use Nette\Http\IRequest;
use \App\Model\User;
use \App\Components\RegistrationForm\IRegistrationFormFactory;

class RegistrationPresenter extends BasePresenter
{
    /** @var IRequest */
    protected $httpRequest;

    /** @var User */
    protected $userModel;

    /** @var Challenge */
    protected $challengeModel;

    /** @var IRegistrationFormFactory @inject */
    public $registrationFormFactory;

    /** @var Friend */
    protected $friendModel;


    /**
     * @param \App\Model\User $userModel
     */
    public function __construct(IRequest $httpRequest, User $userModel, Challenge $challengeModel, Friend $friendModel)
    {
        parent::__construct();
        $this->httpRequest = $httpRequest;
        $this->userModel = $userModel;
        $this->challengeModel = $challengeModel;
        $this->friendModel = $friendModel;
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
        $result = $this->userModel->findRow(98);
        if ($result) {
            $challengeId = $this->httpRequest->getCookie('invitationChallenge');
            $hash = $this->httpRequest->getCookie('invitationHash');

            if ($hash && $challengeId) {
                $challenge = $this->challengeModel->findRow($challengeId);
                if ($hash === Utilities::generateInvitationHash($challengeId, $challenge->created_at)) {
                    $this->friendModel->addFriend($challenge->created_by, $result->id);
                    $this->challengeModel->addUserToChallenge($challengeId, $result->id, $challenge->created_by);

                    $this->flashMessage('The account is active. The challenge is waiting...', parent::MESSAGE_TYPE_INFO);
                    $httpResponse = $this->getHttpResponse();
                    $httpResponse->deleteCookie('invitationChallenge');
                    $httpResponse->deleteCookie('invitationHash');
                    $loginUrl = $this->link('Login:default');
                    $httpResponse->redirect($loginUrl);
                    exit;
                }
            }
            $this->flashMessage('Congratulations, your account has been activated!', parent::MESSAGE_TYPE_INFO);
            $this->redirect('Login:default');

        } else {
            $this->flashMessage('We are sorry, your activated link is wrong.', parent::MESSAGE_TYPE_ERROR);
            $this->redirect('Registration:default');
        }
    }

    public function actionInvitation($challengeId, $hash)
    {
        $challenge = $this->challengeModel->findRow($challengeId);

        if ($challenge && $hash === Utilities::generateInvitationHash($challengeId, $challenge->created_at)) {
            $httpResponse = $this->getHttpResponse();
            $httpResponse->setCookie('invitationChallenge', $challengeId, '100 days');
            $httpResponse->setCookie('invitationHash', $hash, '100 days');
            $registrationUrl = $this->link('Registration:default');
            $httpResponse->redirect($registrationUrl);
            exit;
        } else {
            $this->redirect('Registration:errorInvitation');
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