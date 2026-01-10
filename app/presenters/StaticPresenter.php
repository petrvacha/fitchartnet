<?php

namespace App\Presenters;

/**
 * Static presenter
 */
class StaticPresenter extends BasePresenter
{
    protected $httpResponse;

    protected $httpRequest;


    public function __construct(\Nette\DI\Container $context)
    {
        $this->httpResponse = $context->getByType('Nette\Http\Response');
        $this->httpRequest = $context->getByType('Nette\Http\Request');
    }

    /**
     * @param string $picture
     */
    public function actionGetProfilePicture($picture)
    {
        $path = USER_AVATAR_DIR . '/' . $picture;

        if (!file_exists($path) || !$this->getUser()->isLoggedIn() || !$picture) {
            $path = USER_AVATAR_DIR . '/no-photo-available.png';
        }

        $this->setPictureResponse($path);

        $this->terminate();
    }

    /**
     * @param string $picture
     */
    public function actionGetPicture($picture)
    {
        $path = USER_AVATAR_DIR . '/' . $picture;

        if (file_exists($path) && $picture && $this->getUser()->isLoggedIn()) {
            $this->setPictureResponse($path);
        } else {
            $this->httpResponse->setCode(\Nette\Http\Response::S404_NOT_FOUND);
        }

        $this->terminate();
    }

    /**
     * @param string $picture
     */
    public function actionGetOriginProfilePicture($picture)
    {
        $path = USER_ORIGIN_AVATAR_DIR . '/' . $picture;

        if (file_exists($path) && $picture && $this->getUser()->isLoggedIn()) {
            $this->setPictureResponse($path);
        } else {
            $this->httpResponse->setCode(\Nette\Http\Response::S404_NOT_FOUND);
        }

        $this->terminate();
    }

    /**
     * @param string $path
     */
    private function setPictureResponse($path)
    {
        $fp = fopen($path, 'rb');
        $size = getimagesize($path);
        fclose($fp);
        $this->httpResponse->setHeader('Pragma', null);
        $this->httpResponse->setHeader('Cache-Control', 'max-age=86400');
        $this->httpResponse->setContentType('Content-Type', $size['mime']);
        $this->httpResponse->setContentType('Content-Length', filesize($path));


        $context = new \Nette\Http\Context($this->httpRequest, $this->httpResponse);

        $mTime = filemtime($path);
        if ($context->isModified($mTime)) {
            readfile($path);
        }
    }

    public function renderOffline()
    {
        $this->httpResponse->setHeader('X-Robots-Tag', 'noindex');
        $this->setLayout('launch');
        $this->template->title = 'Offline';
    }
}
