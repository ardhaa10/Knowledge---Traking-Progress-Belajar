<?php
require_once __DIR__ . '/../app/vendor/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../app/vendor/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../app/vendor/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public static function send($to, $subject, $body)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'knowledge.notif@gmail.com';
            $mail->Password = 'cwwjkwfubbozkycy'; // APP PASSWORD
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('knowledge.notif@gmail.com', 'Knowledge');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return ['status' => 'sent', 'error' => null];
        } catch (Exception $e) {
            return ['status' => 'failed', 'error' => $mail->ErrorInfo];
        }
    }
}
