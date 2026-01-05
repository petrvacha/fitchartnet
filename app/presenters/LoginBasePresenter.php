<?php

namespace App\Presenters;
use App\Model\Notification;
use Nette\Database\Context;
use Nette\Http\IRequest;


/**
 * Login Base presenter for all application presenters.
 */
abstract class LoginBasePresenter extends BasePresenter
{

    /** @var Notification */
    protected $notificationModel;

    /**
     * @param Notification $notificationModel
     */
    public function __construct(Notification $notificationModel)
    {
        $this->notificationModel = $notificationModel;
    }

    public function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->getUser()->logout();
            $this->redirect('Homepage:default');
        }
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->notifications = $this->notificationModel->getNewNotifications();
        $this->template->notificationCount = count($this->template->notifications);
        $this->template->userData = $this->getUser()->identity->data;
    }

    public function actionLogout()
    {
        $this->user->logout();
        $this->redirect('Homepage:launch');
    }

    public function handleSeenAllNotifications()
    {
        $this->notificationModel->setSeenAll();
        $this->template->notifications = $this->notificationModel->getNewNotifications();
        $this->redrawControl('wrapper');
        $this->redrawControl('notification');
    }

    public function actionSeenNotification($id)
    {
        $notification = $this->notificationModel->findRow($id);
        $this->notificationModel->findBy(['link' => $notification->link])->update(['seen' => TRUE]);
        $this->redirectUrl($notification->link);
    }
}
