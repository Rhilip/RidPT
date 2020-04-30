<?php
/**
 *
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/6
 * Time: 17:30
 */

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Soundasleep\Html2Text;
use Soundasleep\Html2TextException;

class Mailer
{
    public string $from;
    public string $fromname;

    public PHPMailer $_mailer;

    public function __construct(PHPMailer $mailer)
    {
        $this->_mailer = $mailer;
    }

    public function send(array $receivers, string $subject, string $body)
    {
        $mail = clone $this->_mailer;
        try {
            //Recipients
            $mail->setFrom($this->from, $this->fromname);
            foreach ($receivers as $receiver) {
                if (is_array($receiver)) {  // ['address','name']
                    $mail->addAddress($receiver[0], $receiver[1]);
                } else {  // 'address'
                    $mail->addAddress($receiver);
                }
            }

            // Content
            $mail->isHTML(true);  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;
            try {
                $mail->AltBody = Html2Text::convert($body, ['ignore_errors' => true]);
            } catch (Html2TextException $e) {
                $mail->AltBody = $body;
            }

            return $mail->send();
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
