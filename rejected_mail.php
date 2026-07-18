<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
try {
    //Create a new PHPMailer instance
    $mail3 = new PHPMailer(true);
    //Server settings
    $mail3->SMTPDebug = 0;                 // Enable verbose debug output
    $mail3->isSMTP();                                            // Send using SMTP
    $mail3->Host       = 'websmtp.simply.com';                  // Set the SMTP server to send through
    $mail3->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail3->Username   = 'ue334094@skelby-forsamlingshus.dk';                                      // SMTP username
    $mail3->Password   = '***REMOVED-LEAKED-SMTP-PASSWORD***';                                     // SMTP password
    $mail3->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       // Enable TLS encryption; PHPMailer::ENCRYPTION_SMTPS also accepted
    $mail3->Port       = 587;
    $mail3->CharSet = 'UTF-8';
    $mail3->isHTML(true);
    //Set who the message is to be sent from
    $mail3->setFrom('ue334094@skelby-forsamlingshus.dk', 'Forsamlingshuset');
    $mail3->addReplyTo(getenv('MAIL_REPLY_TO') ?: 'kasserer@skelby-forsamlingshus.dk', 'Kasserer');
    $mail3->addAddress($mailer, 'Gæst');
    $bccDev = getenv('MAIL_BCC_DEV') ?: '';
    if ($bccDev !== '') {
        $mail3->addBCC($bccDev);
    }
    $mail3->Subject = "Anullering  af Bestilling";
    $mail3->Body = 'Kære ' . $name .
        '<br/> Vi har bekræftet din booking til ' . $dato . 'er anulleret af vores reræsentant.<br/>' .
        'M.v.h. fra  Skelby forsamlingshus på Falster';
    $mail3->send();
} catch (Exception $e) {
    echo "Message could not be sent. mailer3 Error: {$mail3->ErrorInfo}";
}
