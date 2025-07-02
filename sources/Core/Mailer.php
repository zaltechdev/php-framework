<?php

namespace App\Core;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer{

    public static function send(string $recepient, string $subject, string $message): bool{
        $hostname = env("mailer_hostname");
        $username = env("mailer_username");
        $password = env("mailer_password");
        $portnumber = env("mailer_portnumber");
        $sendername = env("mailer_sendername");

        $mail = new PHPMailer(true);

        try{
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = $hostname;
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $portnumber;
            
            $mail->setFrom($username,$sendername);
            $mail->addAddress($recepient);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
        
            return $mail->send();
        }
        catch(Exception $error){            
            Logging::record("error",$error,self::class);
            return false;
        }
    }
}