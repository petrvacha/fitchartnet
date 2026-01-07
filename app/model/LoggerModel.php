<?php

namespace App\Model;

use Fitchart\Application\ILogger;
use Nette\Mail\Message;

class LoggerModel extends BaseModel implements ILogger
{
    /** @const EMAIL_TEMPLATE_RELEASE string */
    public const EMAIL_TEMPLATE_RELEASE = 'release';

    /** @const EMAIL_TEMPLATE_NEW_ACCOUNT string */
    public const EMAIL_TEMPLATE_NEW_ACCOUNT = 'new_account';

    /** @const EMAIL_TEMPLATE_RESET_PASSWORD string */
    public const EMAIL_TEMPLATE_RESET_PASSWORD = 'reset_password';

    
    /** @const EMAIL_TABLE string */
    public const EMAIL_TABLE = 'email';


    /**
     * @param Message $mail
     */
    public function mailLog(Message $mail)
    {
        $this->context->table(self::EMAIL_TABLE)->insert([
           'to' => $mail->getHeader('To'),
           'subject' => $mail->getSubject(),
           'body' => $mail->getBody(),
           'sentAt' => $this->getDateTime()
        ]);
    }

    /**
     * @param int $userId 0 === system
     * @param string $activity
     * @param array $data
     */
    public function log($userId, $activity, array $data = [])
    {
        $insert = [
            'userId' => $userId,
            'activity' => $activity
        ];
        if (!empty($data)) {
            $insert['data'] = serialize($data);
        }
        $this->context->table($this->getTableName())->insert($insert);
    }
}
