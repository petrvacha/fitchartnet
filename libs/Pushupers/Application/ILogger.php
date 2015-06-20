<?php

namespace Pushupers\Application;

interface ILogger
{
    /**
     * @param \Nette\Mail\Message $mail
     */
    public function mailLog(\Nette\Mail\Message $mail);

    /**
     * @param int $userId
     * @param string $activity
     * @param array $data
     */
    public function log($userId, $activity, array $data = []);
}
