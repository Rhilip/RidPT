<?php
/**
 *
 * TODO Use phpmailer/phpmailer to replace swiftmailer/swiftmailer
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/6
 * Time: 17:30
 */

namespace Rid\Libraries;

use Rid\Base\BaseObject;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer extends BaseObject
{
    public $host;
    public $port;
    public $username;
    public $password;
    public $encryption;

    public $from;
    public $nikename;

    /** @var Swift_SmtpTransport */
    public $_transport;

    /** @var Swift_Mailer */
    public $_mailer;

    public function onConstruct()
    {
        $this->_transport = (new Swift_SmtpTransport($this->host, $this->port, $this->encryption))
            ->setUsername($this->username)
            ->setPassword($this->password);

        $this->_mailer = new Swift_Mailer($this->_transport);
    }

    public function send(array $receiver, string $subject, string $body)
    {
        $message = (new Swift_Message($subject))
            ->setFrom([$this->from])
            ->setTo($receiver)
            ->setBody($body);

        $result = $this->_mailer->send($message);

        return $result;

    }
}
