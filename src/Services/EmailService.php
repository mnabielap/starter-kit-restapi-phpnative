<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use App\Config\Config;

class EmailService
{
    public static function sendEmail($to, $subject, $text)
    {
        $mail = new PHPMailer(true);
        $config = Config::get('email');

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $config['smtp']['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['smtp']['auth']['user'];
            $mail->Password   = $config['smtp']['auth']['pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $config['smtp']['port'];

            // Recipients
            $mail->setFrom($config['from']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(false); // Plain text for simplicity
            $mail->Subject = $subject;
            $mail->Body    = $text;

            $mail->send();
        } catch (\Exception $e) {
            // Log error but don't crash app
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}