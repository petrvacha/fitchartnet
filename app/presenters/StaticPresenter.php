<?php

namespace App\Presenters;


/**
 * Static presenter
 */
class StaticPresenter extends BasePresenter
{
    /** @var Nette\Http\Response */
    protected $httpResponse;


    /**
     * @param \Nette\Database\Context $context
     */
    public function __construct(\Nette\DI\Container $context)
    {
        parent::__construct();
        $this->httpResponse = $context->getByType('Nette\Http\Response');
    }

    /**
     * @param string $picture
     */
    public function actionGetProfilePicture($picture)
    {
        $path = USER_AVATAR_DIR . '/' . $picture;

        if (!file_exists($path)|| !$this->getUser()->isLoggedIn() || !$picture) {
            $path = USER_AVATAR_DIR . '/no-photo-available.png';
        }

        $fp = fopen($path, 'rb');
        $size = getimagesize($path);
        $this->httpResponse->setContentType('Content-Type', $size['mime']);
        $this->httpResponse->setContentType('Content-Length', filesize($path));
        fpassthru($fp);
        fclose($fp);

        $this->terminate();
    }

    /**
     * @param string $picture
     */
    public function actionGetPicture($picture)
    {
        $path = USER_AVATAR_DIR . '/' . $picture;

        if (file_exists($path) && $picture && $this->getUser()->isLoggedIn()) {
            $fp = fopen($path, 'rb');
            $size = getimagesize($path);
            $this->httpResponse->setContentType('Content-Type', $size['mime']);
            $this->httpResponse->setContentType('Content-Length', filesize($path));
            fpassthru($fp);
            fclose($fp);

        } else {
            $this->httpResponse->setCode(\Nette\Http\Response::S404_NOT_FOUND);
        }

        $this->terminate();
    }
}
