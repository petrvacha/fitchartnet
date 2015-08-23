<?php

namespace App\Model;

use \Nette\Mail\Message;
use Pushupers\Application\ILogger;

class LoggerModel extends BaseModel implements ILogger
{
    /** @const EMAIL_TEMPLATE_RELEASE string */
    const EMAIL_TEMPLATE_RELEASE = 'release';

    /** @const EMAIL_TEMPLATE_NEW_ACCOUNT string */
    const EMAIL_TEMPLATE_NEW_ACCOUNT = 'new_account';

    /** @const EMAIL_TEMPLATE_RESET_PASSWORD string */
    const EMAIL_TEMPLATE_RESET_PASSWORD = 'reset_password';

    
    /** @const EMAIL_TABLE string */
    const EMAIL_TABLE = 'email';


    /**
     * @param Message $mail
     */
    public function mailLog(Message $mail)
    {
        $this->context->table(self::EMAIL_TABLE)->insert(array(
           'to' => $mail->getHeader('To'),
           'subject' => $mail->getSubject(),
           'body' => $mail->getBody(),
           'sentAt' => $this->getDateTime()
        ));
    }

    /**
     * @param int $userId 0 === system
     * @param string $activity
     * @param array $data
     */
    public function log($userId, $activity, array $data = [])
    {
        $insert = array(
            'userId' => $userId,
            'activity' => $activity
        );
        if (!empty($data)) {
            $insert['data'] = serialize($data);
        }
        $this->context->table($this->getTableName())->insert($insert);
    }


}