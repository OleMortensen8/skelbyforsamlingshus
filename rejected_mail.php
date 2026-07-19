<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
try {
    $mailHost = getenv('MAIL_HOST');
    $mailUsername = getenv('MAIL_USERNAME');
    $mailPassword = getenv('MAIL_PASSWORD');
    $mailFrom = getenv('MAIL_FROM');

    if (!$mailHost || !$mailUsername || !$mailPassword || !$mailFrom) {
        throw new \RuntimeException('Missing required mail configuration environment variable(s) (MAIL_HOST/MAIL_USERNAME/MAIL_PASSWORD/MAIL_FROM); rejection email not sent.');
    }

    //Create a new PHPMailer instance
    $mail3 = new PHPMailer(true);
    //Server settings
    $mail3->SMTPDebug = 0;                 // Enable verbose debug output
    $mail3->isSMTP();                                            // Send using SMTP
    $mail3->Host       = $mailHost;                              // Set the SMTP server to send through
    $mail3->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail3->Username   = $mailUsername;                          // SMTP username
    $mail3->Password   = $mailPassword;                          // SMTP password
    $mail3->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       // Enable TLS encryption; PHPMailer::ENCRYPTION_SMTPS also accepted
    $mail3->Port       = (int)(getenv('MAIL_PORT') ?: 587);
    $mail3->CharSet = 'UTF-8';
    $mail3->isHTML(true);
    //Set who the message is to be sent from
    $mail3->setFrom($mailFrom, 'Forsamlingshuset');
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
} catch (\Throwable $e) {
    error_log('rejected_mail.php Exception: ' . $e->getMessage());
    echo "Message could not be sent.";
}
