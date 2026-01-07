<?php

namespace App\Components;

use App\Model\User;
use Nette;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;

class RegistrationForm extends \Fitchart\Application\Control
{
    /** @var integer */
    private $minimumTime = 4;

    /** @var integer */
    private $maximumTime = 300;

    /** @var  array */
    protected $parameters;

    /** @var User */
    protected $userModel;

    /** @var MailerManagerFactory */
    public $mailerManagerFactory;


    /**
     * RegistrationForm constructor.
     * @param User $userModel
     * @param MailerManagerFactory $mailerManagerFactory
     * @param Container $container
     */
    public function __construct(User $userModel, MailerManagerFactory $mailerManagerFactory, Container $container)
    {
        $this->userModel = $userModel;
        $this->mailerManagerFactory = $mailerManagerFactory;
        $this->parameters = $container->getParameters();
    }

    /**
     * @return Form
     */
    public function createComponentRegistrationForm()
    {
        $form = new Form();
        $form->addProtection();

        $form->addText('username', 'Username')
            ->setRequired('Please enter your username.')
            ->addRule(Form::MIN_LENGTH, '%label must be at least %s characters.', 4)
            ->addRule([$this, 'isUsernameAvailable'], 'This username is already taken!')
            ->addRule([$this, 'isNormalUsername'], 'C\'mon this is really not normal.')
            ->setAttribute('placeholder', 'Username');

        $form->addText('email', 'Email')
            ->setRequired('Please enter your email.')
            ->addRule(Form::EMAIL, 'Doesn\'t look like a valid email.')
            ->addRule([$this, 'isEmailAvailable'], 'This email is already taken!')
            ->setAttribute('placeholder', 'Email Address');

        $form->addPassword('password', 'Password')
            ->setRequired('Please enter your password.')
            ->addRule(Form::MIN_LENGTH, 'Password must be at least %s characters.', 6)
            ->setAttribute('placeholder', 'password');

        $form->addHidden('address')
            ->addRule([$this, 'isTimeFast'], 'C\'mon this was really fast.')
            ->addRule([$this, 'isTimeSlow'], 'Session has expired. Try it again.')
            ->setValue(base64_encode(json_encode(time() * $this->parameters['salt_number'])));


        $form->addSubmit('submit', 'Sign Up');

        $form->onSuccess[] = [$this, 'formSent'];

        $this->addBootstrapStyling($form);
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->render();
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function formSent(Form $form, ArrayHash $values)
    {
        try {
            $userData = $this->userModel->add($values);
            $mailerManager = $this->mailerManagerFactory->init();
            $mailerManager->action(MailerManager::REGISTRATION_NEW_USER, $userData);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $form->addError($e->getMessage());
        } catch (\Exception $e) {
            $form->addError($e->getMessage());
        }
    }

    /**
     * @param \Nette\Forms\IControl $userNameCandidate
     * @return bool
     */
    public function isUsernameAvailable($userNameCandidate)
    {
        return $this->userModel->findOneBy(['username' => $userNameCandidate->value]) ? false : true;
    }

    /**
     * @param \Nette\Forms\IControl $timetrap
     * @return bool
     */
    public function isTimeFast($timetrap)
    {
        $timetrap = json_decode(base64_decode($timetrap->value));

        if (is_numeric($timetrap)) {
            $time = time();
            $diff = $time - $timetrap / $this->parameters['salt_number'];
            if ($diff >= $this->minimumTime) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \Nette\Forms\IControl $timetrap
     * @return bool
     */
    public function isTimeSlow($timetrap)
    {
        $timetrap = json_decode(base64_decode($timetrap->value));

        if (is_numeric($timetrap)) {
            $time = time();
            $diff = $time - $timetrap / $this->parameters['salt_number'];
            if ($diff <= $this->maximumTime) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \Nette\Forms\IControl $userNameCandidate
     * @return bool
     */
    public function isNormalUsername($userNameCandidate)
    {
        if (count(explode(" ", $userNameCandidate->value)) > 4) {
            return false;
        }

        if (strpos($userNameCandidate->value, "$")) {
            return false;
        }

        return true;
    }

    /**
     * @param \Nette\Forms\IControl $emailCandidate
     * @return bool
     */
    public function isEmailAvailable($emailCandidate)
    {
        return $this->userModel->findOneBy(['email' => $emailCandidate->value]) ? false : true;
    }
}
