<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/6
 * Time: 17:30
 */

namespace apps\common\components;


use Mix\Base\Component;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class MailerComponent extends Component
{
    public $host;
    public $port;
    public $username;
    public $password;
    public $encryption;

    public $from;
    public $nikename;

    public $_transport;
    public $_mailer;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->_transport = (new Swift_SmtpTransport($this->host, $this->port,$this->encryption))
            ->setUsername($this->username)
            ->setPassword($this->password);

        $this->_mailer = new Swift_Mailer($this->_transport);
    }

    public function send(array $receiver,string $subject,string $body)
    {
        $message = (new Swift_Message($subject))
            ->setFrom([$this->from])
            ->setTo($receiver)
            ->setBody($body);

        $result = $this->_mailer->send($message);

        return $result;

    }
}